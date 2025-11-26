<?php

namespace NckRtl\Toolbar;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ToolbarInjector
{
    /**
     * Inject the toolbar into the response
     */
    public function inject(Request $request, $response): void
    {
        if (! Toolbar::isEnabled()) {
            return;
        }

        if ($this->isInertiaRequest($request)) {
            $this->injectToolbarData($response);

            return;
        }

        $this->injectToolbarHtml($request, $response);
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
    protected function injectToolbarData($response): Response|JsonResponse
    {
        // Support both Response and JsonResponse (Inertia uses JsonResponse)
        if (! isset($response->headers)) {
            return $response;
        }

        // Collect data
        $data = new CollectorManager(response: $response)->collectData();

        // Add debug data as a custom header
        $response->headers->set(
            'x-toolbar',
            base64_encode(json_encode($data))
        );

        return $response;
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

        // Check if response has closing body tag
        $content = $response->getContent();

        if (! str_contains($content, '</body>')) {
            return false;
        }

        return true;
    }

    /**
     * Inject the debugbar into the response
     */
    protected function injectToolbarHtml(Request $request, Response|JsonResponse $response): void
    {
        if (! $this->shouldInjectHtml($request, $response)) {
            return;
        }

        // Collect data
        $data = new CollectorManager(response: $response)->collectData();

        $content = $response->getContent();

        // Inject before closing body tag
        $position = strripos($content, '</body>');

        // Generate the debugbar HTML
        $toolbarHtml = $this->getToolbarHtml(
            json_encode(
                $data,
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            )
        );

        if ($position !== false) {
            $content = substr($content, 0, $position).$toolbarHtml.substr($content, $position);
            $response->setContent($content);

            // Update the content length
            $response->headers->set('Content-Length', (string) strlen($content));
        }
    }

    /**
     * Get the package's Vite dev server URL from the hot file
     */
    protected function getPackageViteUrl(): ?string
    {
        $hotFile = __DIR__.'/../hot';

        if (file_exists($hotFile)) {
            $viteUrl = trim(file_get_contents($hotFile));

            // Verify the server is actually running by checking @vite/client
            if ($this->checkViteUrl($viteUrl.'/@vite/client')) {
                return $viteUrl;
            }
        }

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

        $jsUrl = route('toolbar.assets', ['asset' => $assets['js']]);
        $cssUrl = route('toolbar.assets', ['asset' => $assets['css']]);

        $script = "<script src=\"{$jsUrl}\"{$nonceAttribute}></script>";

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
        $script = "<script type=\"module\" src=\"{$viteUrl}/resources/js/toolbar.dev.js\"{$nonceAttribute}></script>";

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

        return <<<HTML
        {$comment}
        <div id="laravel-toolbar-shadow-host"></div>
        <script{$nonceAttribute}>
            window.__LARAVEL_TOOLBAR_DATA__ = {$data};
            window.__LARAVEL_TOOLBAR_CSS_URL__ = "{$cssUrl}";

            (function() {
                var cached = sessionStorage.getItem('laravel-toolbar-html-cache');
                var cachedCss = sessionStorage.getItem('laravel-toolbar-css-cache');

                if (cached && cachedCss) {

                    // Strip inline styles - Vue will re-add them when it hydrates
                    if("{$nonce}" !== null)
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
    protected function getProductionManifestAssets(): array
    {
        $manifestPath = __DIR__.'/../build/manifest.json';

        if (! file_exists($manifestPath)) {
            return ['js' => '', 'css' => ''];
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $entry = $manifest['resources/js/toolbar.prod.ts'] ?? null;

        if (! $entry) {
            return ['js' => '', 'css' => ''];
        }

        return [
            'js' => basename($entry['file'] ?? ''),
            'css' => basename($entry['css'][0] ?? ''),
        ];
    }
}
