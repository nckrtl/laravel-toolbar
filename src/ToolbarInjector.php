<?php

declare(strict_types=1);

namespace NckRtl\Toolbar;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use NckRtl\Toolbar\Support\ProfileRequestContext;
use NckRtl\Toolbar\Support\ProfileSummaryBuilder;
use NckRtl\Toolbar\Support\RedirectChainStore;
use NckRtl\Toolbar\Support\RequestHistoryRowFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ToolbarInjector
{
    /**
     * Cached Vite dev server URL check result
     */
    protected static ?string $cachedViteUrl = null;

    /**
     * Timestamp when the Vite URL cache was last updated
     */
    protected static ?float $viteUrlCacheTime = null;

    /**
     * How long to cache the Vite URL check (in seconds)
     * 30 seconds balances performance with responsiveness to dev server restarts
     */
    protected const VITE_URL_CACHE_TTL = 30;

    /**
     * Inject the toolbar into the response
     */
    public function inject(Request $request, $response): void
    {
        if (! Toolbar::isEnabled()) {
            return;
        }

        $context = ProfileRequestContext::fromRequest($request);

        if ($context->requestId !== null) {
            $snapshotRequestId = $this->resolveProfiledRequestId($request);

            $this->trackRedirectChainForProfiledRequest($request, $response, $snapshotRequestId);
            $this->handleProfiledRequest($request, $response, $context, $snapshotRequestId);

            return;
        }

        if (! Toolbar::$visible) {
            new CollectorManager(response: $response)->collectData();

            return;
        }

        if ($this->shouldInjectHeader($request, $response)) {
            $this->injectToolbarData($request, $response);

            return;
        }

        $shouldInjectHtml = $this->shouldInjectHtml($request, $response);

        if (! $shouldInjectHtml && ! $this->isTrackedRedirectResponse($response)) {
            $this->clearRedirectChainIfPresent($request, $response);

            return;
        }

        $data = $this->collectToolbarData($request, $response, canExposeToolbarData: $shouldInjectHtml);

        if ($shouldInjectHtml) {
            $this->injectToolbarHtml($request, $response, $data);
        }
    }

    /**
     * For profiled requests (X-REQUEST-ID present): build a lightweight summary
     * from profiler checkpoints, set it as a response header, then defer the
     * full collection + cache write to after the response is sent.
     */
    protected function handleProfiledRequest(
        Request $request,
        $response,
        ProfileRequestContext $context,
        string $snapshotRequestId,
    ): void {
        $summary = ProfileSummaryBuilder::build($request);
        $summary['request_id'] = $snapshotRequestId;
        $summary['profile_request_id'] = $context->requestId;
        $summary['history_row'] = array_merge(
            app(RequestHistoryRowFactory::class)->fromRequest($request, $response, $snapshotRequestId),
            [
                'duration' => data_get($summary, 'profiler.total_wall_time'),
            ],
        );

        if (isset($response->headers)) {
            $response->headers->set(
                'X-Toolbar-Summary',
                base64_encode((string) json_encode($summary, JSON_THROW_ON_ERROR)),
            );
        }

        app()->terminating(function () use ($response) {
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            new CollectorManager(response: $response)->collectData();
        });
    }

    /**
     * Check if this is an Inertia request
     */
    protected function isInertiaRequest(Request $request): bool
    {
        return $request->header('X-Inertia') === 'true';
    }

    /**
     * Add debug data to Inertia response via header
     */
    protected function injectToolbarData(Request $request, $response): Response|JsonResponse|RedirectResponse
    {
        // Support both Response and JsonResponse (Inertia uses JsonResponse)
        if (! isset($response->headers)) {
            return $response;
        }

        $data = $this->collectToolbarData(
            $request,
            $response,
            canExposeToolbarData: true,
            headerOnly: true,
        );

        // Add debug data as a custom header
        $response->headers->set(
            'x-toolbar',
            base64_encode(json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}')
        );

        return $response;
    }

    protected function shouldInjectHeader(Request $request, $response): bool
    {
        if (! isset($response->headers)) {
            return false;
        }

        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return false;
        }

        return $this->isToolbarHeaderRequest($request);
    }

    /**
     * Determine if the debugbar should be injected
     */
    protected function shouldInjectHtml(Request $request, $response): bool
    {

        // Don't inject for AJAX requests
        if ($request->ajax()) {
            return false;
        }

        // Don't inject for non-HTML responses
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return false;
        }

        if (! $response instanceof Response) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type');

        if ($contentType && ! str_contains($contentType, 'html')) {
            return false;
        }

        // Check if response has closing body tag (case-insensitive)
        $content = $response->getContent();

        if (stripos($content, '</body>') === false) {
            return false;
        }

        return true;
    }

    /**
     * Inject the debugbar into the response
     */
    protected function injectToolbarHtml(Request $request, $response, ?array $data = null): void
    {
        if (! $this->shouldInjectHtml($request, $response)) {
            return;
        }

        // Collect data
        $data ??= $this->collectToolbarData($request, $response, canExposeToolbarData: true);

        $content = $response->getContent();

        // Inject before closing body tag
        $position = strripos($content, '</body>');

        // Generate the debugbar HTML
        $json = json_encode(
            $data,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
        );

        $toolbarHtml = $this->getToolbarHtml($json ?: '{}');

        if ($position !== false) {
            $content = substr($content, 0, $position).$toolbarHtml.substr($content, $position);
            $response->setContent($content);

            // Update the content length
            $response->headers->set('Content-Length', (string) strlen($content));
        }
    }

    protected function collectToolbarData(
        Request $request,
        $response,
        bool $canExposeToolbarData = true,
        bool $headerOnly = false,
    ): array {
        $data = new CollectorManager(response: $response)->collectData();

        return $this->attachRequestHistory($request, $response, $data, $canExposeToolbarData, $headerOnly);
    }

    protected function attachRequestHistory(
        Request $request,
        $response,
        array $data,
        bool $canExposeToolbarData,
        bool $headerOnly,
    ): array {
        if (! isset($response->headers)) {
            return $headerOnly ? [] : $data;
        }

        $requestId = $this->resolveRequestId($data);
        $historyRow = app(RequestHistoryRowFactory::class)->fromPayload($request, $data);

        $data['request_id'] = $requestId;
        unset($data['redirect_chain']);

        $history = [$historyRow];

        try {
            $store = app(RedirectChainStore::class);

            if ($this->isTrackedRedirectResponse($response)) {
                $chainId = $store->currentChainId($request) ?? $store->createChainId();

                $store->append($chainId, $historyRow);
                $response->headers->setCookie($store->makeChainCookie($chainId));

                return $headerOnly
                    ? $this->compactHeaderPayload($requestId, $historyRow)
                    : $data;
            }

            $chainId = $store->currentChainId($request);

            if ($chainId === null) {
                if ($headerOnly) {
                    return $this->shouldResetHeaderHistory($historyRow)
                        ? $this->replacementHeaderPayload($data, $requestId, $history)
                        : $this->compactHeaderPayload($requestId, $historyRow);
                }

                $data['selected_request_id'] = $requestId;
                $data['request_history'] = $history;

                return $data;
            }

            if (! $canExposeToolbarData) {
                $store->forget($chainId);
                $response->headers->setCookie($store->makeForgetCookie());

                return $headerOnly
                    ? $this->compactHeaderPayload($requestId, $historyRow)
                    : $data;
            }

            $history = $store->consume($chainId, $historyRow);
            $response->headers->setCookie($store->makeForgetCookie());
        } catch (\Throwable $e) {
            report($e);
        }

        if ($headerOnly) {
            return $this->shouldResetHeaderHistory($historyRow)
                ? $this->replacementHeaderPayload($data, $requestId, $history)
                : $this->compactHeaderPayload($requestId, $historyRow);
        }

        $data['selected_request_id'] = $requestId;
        $data['request_history'] = $history;

        return $data;
    }

    protected function isTrackedRedirectResponse($response): bool
    {
        if (! isset($response->headers) || ! method_exists($response, 'getStatusCode')) {
            return false;
        }

        $statusCode = (int) $response->getStatusCode();

        if (! in_array($statusCode, [301, 302, 303, 307, 308], true)) {
            return false;
        }

        $location = $response->headers->get('Location');

        return is_string($location) && $location !== '';
    }

    protected function trackRedirectChainForProfiledRequest(Request $request, $response, string $snapshotRequestId): void
    {
        if (! isset($response->headers) || ! method_exists($response, 'getStatusCode')) {
            return;
        }

        if (! $this->isTrackedRedirectResponse($response)) {
            $this->clearRedirectChainIfPresent($request, $response);

            return;
        }

        try {
            $store = app(RedirectChainStore::class);
            $chainId = $store->currentChainId($request) ?? $store->createChainId();

            $historyRow = app(RequestHistoryRowFactory::class)
                ->fromRequest($request, $response, $snapshotRequestId);

            $store->append($chainId, $historyRow);

            $response->headers->setCookie($store->makeChainCookie($chainId));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function clearRedirectChainIfPresent(Request $request, $response): void
    {
        try {
            $store = app(RedirectChainStore::class);
            $chainId = $store->currentChainId($request);

            if ($chainId === null) {
                return;
            }

            $store->forget($chainId);

            if (isset($response->headers)) {
                $response->headers->setCookie($store->makeForgetCookie());
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function isToolbarHeaderRequest(Request $request): bool
    {
        if ($this->isInertiaRequest($request)) {
            return true;
        }

        if ($request->ajax()) {
            return true;
        }

        return $request->expectsJson();
    }

    protected function resolveRequestId(array $data): string
    {
        $requestId = data_get($data, 'metadata.request_id');

        if (is_string($requestId) && $requestId !== '') {
            return $requestId;
        }

        $id = data_get($data, 'metadata.id');

        return is_string($id) ? $id : '';
    }

    protected function compactHeaderPayload(string $requestId, array $historyRow): array
    {
        return [
            'request_id' => $requestId,
            'history_row' => $historyRow,
        ];
    }

    protected function replacementHeaderPayload(array $data, string $requestId, array $history): array
    {
        $data['request_id'] = $requestId;
        $data['selected_request_id'] = $requestId;
        $data['request_history'] = $history;

        return $data;
    }

    protected function shouldResetHeaderHistory(array $historyRow): bool
    {
        return ($historyRow['is_xhr'] ?? true) === false;
    }

    protected function resolveProfiledRequestId(Request $request): string
    {
        $snapshotRequestId = $request->attributes->get(ProfileRequestContext::SNAPSHOT_REQUEST_ID_ATTRIBUTE);

        if (is_string($snapshotRequestId) && $snapshotRequestId !== '') {
            return $snapshotRequestId;
        }

        $snapshotRequestId = (string) Str::uuid();
        $request->attributes->set(ProfileRequestContext::SNAPSHOT_REQUEST_ID_ATTRIBUTE, $snapshotRequestId);

        return $snapshotRequestId;
    }

    /**
     * Get the package's Vite dev server URL from the hot file
     *
     * Uses caching to avoid blocking HTTP calls on every request.
     */
    protected function getPackageViteUrl(): ?string
    {
        // Check if we have a valid cached result
        if (self::$viteUrlCacheTime !== null) {
            $cacheAge = microtime(true) - self::$viteUrlCacheTime;
            if ($cacheAge < self::VITE_URL_CACHE_TTL) {
                return self::$cachedViteUrl;
            }
        }

        $hotFile = __DIR__.'/../hot';

        if (file_exists($hotFile)) {
            $viteUrl = trim(file_get_contents($hotFile));

            // Verify the server is actually running by checking @vite/client
            if ($this->checkViteUrl($viteUrl.'/@vite/client')) {
                self::$cachedViteUrl = $viteUrl;
                self::$viteUrlCacheTime = microtime(true);

                return $viteUrl;
            }
        }

        // Cache the negative result too
        self::$cachedViteUrl = null;
        self::$viteUrlCacheTime = microtime(true);

        return null;
    }

    /**
     * Check if a URL is accessible
     */
    protected function checkViteUrl(string $url): bool
    {
        try {
            $response = Http::timeout(1)
                ->connectTimeout(1)
                ->withoutVerifying()
                ->head($url);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the debugbar HTML to inject
     */
    protected function getToolbarHtml(string $data): string
    {
        $nonceAttribute = $this->getNonceAttribute();

        if ($viteUrl = $this->getPackageViteUrl()) {
            return $this->toolbarHtmlWithViteAssets(data: $data, viteUrl: $viteUrl, nonceAttribute: $nonceAttribute);
        }

        return $this->toolbarHtmlWithProductionAssets(data: $data, nonceAttribute: $nonceAttribute);
    }

    protected function toolbarHtmlWithProductionAssets(string $data, string $nonceAttribute): string
    {
        $assets = $this->getProductionManifestAssets();

        if (! $assets) {
            return '<!-- Laravel Toolbar assets are missing from the package distribution -->';
        }

        $jsUrl = url('/_toolbar/'.$assets['js']);
        $cssUrl = url('/_toolbar/'.$assets['css']);

        $script = "<script src=\"{$jsUrl}\" data-navigate-once{$nonceAttribute}></script>";

        return $this->toolbarHtml(
            data: $data,
            cssUrl: $cssUrl,
            script: $script,
            nonceAttribute: $nonceAttribute,
            isDev: false
        );
    }

    protected function toolbarHtmlWithViteAssets(string $data, string $viteUrl, string $nonceAttribute): string
    {
        $cssUrl = "{$viteUrl}/resources/css/toolbar.css?inline";
        $script = "<script type=\"module\" src=\"{$viteUrl}/resources/js/toolbar.dev.js\" data-navigate-once{$nonceAttribute}></script>";

        return $this->toolbarHtml(
            data: $data,
            cssUrl: $cssUrl,
            script: $script,
            nonceAttribute: $nonceAttribute,
            isDev: true
        );
    }

    protected function toolbarHtml(string $data, string $cssUrl, string $script, string $nonceAttribute, bool $isDev = false): string
    {
        $comment = $isDev ? '<!-- Laravel Toolbar (Development Mode with HMR) -->' : '<!-- Laravel Toolbar -->';

        $nonce = Vite::cspNonce() ?: null;

        $additionalLightDomHtml = app(Toolbar::class)->config->additionalLightDomHtml();

        return <<<HTML
        {$comment}

        {$additionalLightDomHtml}

        <div id="laravel-toolbar-shadow-host" data-feedback-toolbar="true"></div>
        <script{$nonceAttribute}>
            window.__LARAVEL_TOOLBAR_DATA__ = {$data};
            window.__LARAVEL_TOOLBAR_CSS_URL__ = "{$cssUrl}";

            (function() {
                var cached = sessionStorage.getItem('laravel-toolbar-html-cache');
                var cachedCss = sessionStorage.getItem('laravel-toolbar-css-cache');

                if (cached && cachedCss) {

                    // Strip inline styles - Vue will re-add them when it hydrates
                    if("{$nonce}" !== "")
                    {
                        cached = cached.replace(/\s*style="[^"]*"/g, '');
                    }

                    var host = document.getElementById('laravel-toolbar-shadow-host');
                    if (host) {
                        var shadow = host.attachShadow({ mode: 'open' });
                        var sheet = new CSSStyleSheet();
                        sheet.replaceSync(cachedCss);
                        shadow.adoptedStyleSheets = [sheet];
                        shadow.innerHTML = '<div id="laravel-toolbar-root">' + cached + '</div>';
                        window.__TOOLBAR_SHADOW_PRECREATED__ = shadow;
                        window.__TOOLBAR_STYLESHEET__ = sheet;
                    }
                }
            })();

            window.dispatchEvent(new CustomEvent('laravel-toolbar:html-updated'));
        </script>
        {$script}
        <!-- End Laravel Toolbar -->
        HTML;
    }

    private function getNonceAttribute(): string
    {
        $nonce = Vite::cspNonce();

        return $nonce ? " nonce=\"{$nonce}\"" : '';
    }

    /**
     * Get the built asset filenames from Vite's manifest
     */
    protected function getProductionManifestAssets(): ?array
    {
        $manifestPath = __DIR__.'/../build/manifest.json';

        if (! file_exists($manifestPath)) {
            return null;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $entry = $manifest['resources/js/toolbar.prod.ts'] ?? null;

        if (! $entry) {
            return null;
        }

        $cssFile = '';
        if (isset($entry['css']) && is_array($entry['css']) && ! empty($entry['css'])) {
            $cssFile = basename($entry['css'][0]);
        }

        $jsFile = basename($entry['file'] ?? '');

        if ($jsFile === '' || $cssFile === '') {
            return null;
        }

        if (
            ! file_exists(__DIR__.'/../build/assets/'.$jsFile)
            || ! file_exists(__DIR__.'/../build/assets/'.$cssFile)
        ) {
            return null;
        }

        return ['js' => $jsFile, 'css' => $cssFile];
    }
}
