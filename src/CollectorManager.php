<?php

declare(strict_types=1);

namespace NckRtl\Toolbar;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Telescope\Telescope;
use NckRtl\Toolbar\Support\ProfileRequestContext;

class CollectorManager
{
    public string $id;

    public bool $telescopeIsInstalled = false;

    public ?Collection $telescopeEntries = null;

    public array $data = [];

    public function __construct(public Response|JsonResponse|RedirectResponse|null $response = null)
    {
        $this->id = (string) Str::uuid();
    }

    /**
     * Get all collected data
     */
    public function collectData(): array
    {
        $toolbar = app(Toolbar::class);
        $request = request();
        $requestContext = ProfileRequestContext::fromRequest($request);
        $snapshotRequestId = $request->attributes->get(ProfileRequestContext::SNAPSHOT_REQUEST_ID_ATTRIBUTE);
        $publicRequestId = is_string($snapshotRequestId) && $snapshotRequestId !== ''
            ? $snapshotRequestId
            : $requestContext->requestId;
        $resolvedAuth = ProfileRequestContext::resolvedAuthFromRequest($request);

        $startTime = microtime(true);

        if (empty($collectors = $toolbar->config->enabledCollectors())) {
            $time = microtime(true);

            $this->data = [
                'metadata' => [
                    'id' => $this->id,
                    'timestamp' => $time,
                    'request_id' => $publicRequestId,
                    'profile_request_id' => $requestContext->requestId,
                    'auth_mode' => $resolvedAuth['auth_mode'],
                    'auth_user_id' => $resolvedAuth['auth_user_id'],
                    'collectors' => 'No collectors enabled in the toolbar configuration (toolbar.php)',
                    'wall_time' => [
                        'total' => number_format(($time - $startTime) * 1000, 2).'ms',
                    ],
                ],
            ];

            $this->cacheCollectedData($publicRequestId, $requestContext->requestId);

            return $this->data;
        }

        if ($toolbar->telescopeIsInstalled()) {
            $this->setTelescopeEntries();
        }

        foreach ($collectors as $collector) {
            $startCollector = microtime(true);

            try {
                $this->data[$collector->key()] = $collector->collectData(
                    collectorManager: $this,
                );
            } catch (\Throwable $e) {
                report($e);

                continue;
            }

            $elapsed = (microtime(true) - $startCollector) * 1_000_000;
            $this->data['metadata']['wall_time']['collectors'][$collector->key()]['duration'] = number_format($elapsed, 2).'µs';
        }

        $this->data['layout'] = $toolbar->config->layout->toArray();

        $this->data['metadata']['id'] = $this->id;
        $this->data['metadata']['request_id'] = $publicRequestId;
        $this->data['metadata']['profile_request_id'] = $requestContext->requestId;
        $this->data['metadata']['auth_mode'] = $resolvedAuth['auth_mode'];
        $this->data['metadata']['auth_user_id'] = $resolvedAuth['auth_user_id'];
        $this->data['metadata']['timing_anchors'] = $this->timingAnchors($request);

        if (app(Toolbar::class)->config->debug) {
            $endTime = microtime(true);

            $this->data['metadata']['debug'] = true;
            $this->data['metadata']['timestamp'] = $endTime;
            $this->data['metadata']['wall_time']['total'] = number_format(($endTime - $startTime) * 1000, 2).'ms';
        }

        $this->cacheCollectedData($publicRequestId, $requestContext->requestId);

        $this->data['metadata']['timing_anchors']['collected_at_ms'] = microtime(true) * 1000;

        return $this->data;
    }

    /**
     * @return array{caddy_start_ms: float|null, php_start_ms: float, laravel_start_ms: float|null, profiler_end_ms: float|null, collected_at_ms: float}
     */
    private function timingAnchors(Request $request): array
    {
        $caddyStart = $request->header('X-Caddy-Start');
        $phpStart = (float) $request->server('REQUEST_TIME_FLOAT', microtime(true));

        $laravelStart = defined('LARAVEL_START') ? (float) LARAVEL_START : null;

        $profilerEnd = null;
        $stages = data_get($this->data, 'profiler.stages', []);

        if (is_array($stages) && $stages !== []) {
            $lastStage = end($stages);
            $endValue = data_get($lastStage, 'end.time.value');

            if (is_numeric($endValue)) {
                $profilerEnd = (float) $endValue * 1000;
            }
        }

        return [
            'caddy_start_ms' => is_numeric($caddyStart) ? (float) $caddyStart : null,
            'php_start_ms' => $phpStart * 1000,
            'laravel_start_ms' => $laravelStart !== null ? $laravelStart * 1000 : null,
            'profiler_end_ms' => $profilerEnd,
            'collected_at_ms' => microtime(true) * 1000,
        ];
    }

    private function cacheCollectedData(?string $requestId, ?string $profileRequestId = null): void
    {
        $cacheKey = $requestId ?? $this->id;
        $plain = json_decode((string) json_encode($this->data), true);
        $plain['metadata']['timing_anchors']['collected_at_ms'] = microtime(true) * 1000;

        Cache::put(
            'laravel-toolbar-request-data-'.$cacheKey,
            $plain,
            config('toolbar.request_data_ttl', 30),
        );

        if ($profileRequestId !== null && $profileRequestId !== '' && $profileRequestId !== $cacheKey) {
            Cache::put(
                'laravel-toolbar-request-data-'.$profileRequestId,
                $plain,
                config('toolbar.request_data_ttl', 30),
            );
        }
    }

    protected function setTelescopeEntries(): bool
    {
        if (empty($entries = Telescope::$entriesQueue)) {
            return false;
        }

        $this->telescopeEntries = collect($entries)->map(function ($entry) {
            return $entry->toArray();
        })->groupBy(function ($entry) {
            return $entry['type'];
        });

        return true;
    }
}
