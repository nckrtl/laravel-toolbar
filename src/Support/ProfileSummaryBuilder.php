<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Support;

use Illuminate\Http\Request;
use NckRtl\Toolbar\Enums\RequestCheckpointId;
use NckRtl\Toolbar\Observers\QueryObserver;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use NckRtl\Toolbar\Toolbar;

final class ProfileSummaryBuilder
{
    /**
     * Build a lightweight summary directly from profiler checkpoints and observers.
     * Skips the full collector pipeline, JSON serialization, and cache write.
     *
     * @return array<string, mixed>
     */
    public static function build(Request $request): array
    {
        $context = ProfileRequestContext::fromRequest($request);
        $resolvedAuth = ProfileRequestContext::resolvedAuthFromRequest($request);
        $snapshotRequestId = $request->attributes->get(ProfileRequestContext::SNAPSHOT_REQUEST_ID_ATTRIBUTE);
        $requestId = is_string($snapshotRequestId) && $snapshotRequestId !== ''
            ? $snapshotRequestId
            : $context->requestId;

        $stages = self::buildStages();
        $totalWallMs = self::totalWallTime($stages);

        $queries = self::buildQuerySummary();

        return [
            'request_id' => $requestId,
            'profile_request_id' => $context->requestId,
            'auth_mode' => $resolvedAuth['auth_mode'],
            'request' => [
                'route_name' => (string) ($request->route()?->getName() ?? $request->route()?->getActionName() ?? '-'),
                'controller_action' => (string) ($request->route()?->getActionName() ?? '-'),
            ],
            'profiler' => [
                'total_wall_time' => self::formatMs($totalWallMs),
                'total_real_memory' => self::formatMemory(),
                'total_allocated_memory' => self::formatAllocatedMemory(),
                'stages' => $stages,
            ],
            'queries' => $queries,
            'timing_anchors' => self::buildAnchors($request),
        ];
    }

    /**
     * @return list<array{label: string, duration: string}>
     */
    private static function buildStages(): array
    {
        $stageDefinitions = [
            ['Bootstrapping', RequestCheckpointId::LARAVEL_START, RequestCheckpointId::BEFORE_SERVICES_PROVIDERS],
            ['Booting services providers', RequestCheckpointId::BEFORE_SERVICES_PROVIDERS, RequestCheckpointId::AFTER_SERVICES_PROVIDERS],
            ['Middleware in', RequestCheckpointId::AFTER_SERVICES_PROVIDERS, RequestCheckpointId::BEFORE_CONTROLLER],
            ['Controller', RequestCheckpointId::BEFORE_CONTROLLER, RequestCheckpointId::BEFORE_VIEW_RENDERING],
            ['View rendering', RequestCheckpointId::BEFORE_VIEW_RENDERING, RequestCheckpointId::AFTER_VIEW_RENDERING],
            ['Middleware out', RequestCheckpointId::AFTER_VIEW_RENDERING, RequestCheckpointId::AFTER_MIDDLEWARE],
            ['Preparing response', RequestCheckpointId::AFTER_MIDDLEWARE, RequestCheckpointId::REQUEST_HANDLED],
        ];

        $stages = [];

        foreach ($stageDefinitions as [$label, $startId, $endId]) {
            $start = Profiler::getCheckpoint($startId);
            $end = Profiler::getCheckpoint($endId);

            if ($start?->time !== null && $end?->time !== null) {
                $ms = ($end->time->value - $start->time->value) * 1000;
                $stages[] = ['label' => $label, 'duration' => self::formatMs($ms)];
            } else {
                $stages[] = ['label' => $label, 'duration' => '0ms'];
            }
        }

        return $stages;
    }

    /**
     * @param  list<array{label: string, duration: string}>  $stages
     */
    private static function totalWallTime(array $stages): float
    {
        $total = 0.0;

        foreach ($stages as $stage) {
            $total += (float) rtrim($stage['duration'], 'ms');
        }

        return $total;
    }

    /**
     * @return array{count: int, slow_count: int, duplicate_count: int}
     */
    private static function buildQuerySummary(): array
    {
        if (! app()->bound(Toolbar::class)) {
            return ['count' => 0, 'slow_count' => 0, 'duplicate_count' => 0];
        }

        $toolbar = app(Toolbar::class);
        $queryObserver = $toolbar->config->getObserver(QueryObserver::class);

        if ($queryObserver === null) {
            return ['count' => 0, 'slow_count' => 0, 'duplicate_count' => 0];
        }

        $slow = 0;
        $duplicate = 0;

        foreach ($queryObserver->queries as $query) {
            if ($query->is_slow ?? false) {
                $slow++;
            }

            if ($query->is_duplicate ?? false) {
                $duplicate++;
            }
        }

        return [
            'count' => count($queryObserver->queries),
            'slow_count' => $slow,
            'duplicate_count' => $duplicate,
        ];
    }

    /**
     * @return array{caddy_start_ms: float|null, php_start_ms: float, laravel_start_ms: float|null, profiler_end_ms: float|null, collected_at_ms: float}
     */
    private static function buildAnchors(Request $request): array
    {
        $caddyStart = $request->header('X-Caddy-Start');
        $phpStart = (float) $request->server('REQUEST_TIME_FLOAT', microtime(true));
        $laravelStart = defined('LARAVEL_START') ? (float) LARAVEL_START : null;

        $requestHandled = Profiler::getCheckpoint(RequestCheckpointId::REQUEST_HANDLED);
        $profilerEnd = $requestHandled?->time !== null
            ? $requestHandled->time->value * 1000
            : null;

        return [
            'caddy_start_ms' => is_numeric($caddyStart) ? (float) $caddyStart : null,
            'php_start_ms' => $phpStart * 1000,
            'laravel_start_ms' => $laravelStart !== null ? $laravelStart * 1000 : null,
            'profiler_end_ms' => $profilerEnd,
            'collected_at_ms' => microtime(true) * 1000,
        ];
    }

    private static function formatMs(float $ms): string
    {
        return number_format($ms, 2).'ms';
    }

    private static function formatMemory(): string
    {
        $bytes = memory_get_usage(true);
        $lastCheckpoint = Profiler::getCheckpoint(RequestCheckpointId::REQUEST_HANDLED)
            ?? Profiler::getCheckpoint(RequestCheckpointId::AFTER_MIDDLEWARE);

        if ($lastCheckpoint?->memory_real !== null) {
            $bytes = (int) $lastCheckpoint->memory_real->value;
        }

        return self::formatBytesHuman($bytes);
    }

    private static function formatAllocatedMemory(): string
    {
        return self::formatBytesHuman(memory_get_usage());
    }

    private static function formatBytesHuman(int $bytes): string
    {
        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}
