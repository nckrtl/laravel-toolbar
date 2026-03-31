<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class RequestProfileSummaryData extends Data
{
    public function __construct(
        public string $auth_mode,
        public array $request,
        public array $profiler,
        public array $queries,
        public ?array $timing_anchors = null,
    ) {}

    /**
     * Build from the flattened cached payload (used by the request-data endpoint).
     */
    public static function fromPayload(array $payload): self
    {
        $queries = collect(data_get($payload, 'queries.queries', []));
        $anchors = data_get($payload, 'metadata.timing_anchors');

        return new self(
            auth_mode: (string) data_get($payload, 'metadata.auth_mode', 'guest'),
            request: [
                'route_name' => (string) data_get($payload, 'request.route_name', '-'),
                'controller_action' => (string) data_get($payload, 'request.controller_action', '-'),
            ],
            profiler: [
                'total_wall_time' => (string) data_get($payload, 'profiler.total_wall_time.formattedValue', '0ms'),
                'total_real_memory' => (string) data_get($payload, 'profiler.total_real_memory.formattedValue', '0B'),
                'total_allocated_memory' => (string) data_get($payload, 'profiler.total_allocated_memory.formattedValue', '0B'),
                'stages' => self::summarizeStages(data_get($payload, 'profiler.stages', [])),
            ],
            queries: [
                'count' => $queries->count(),
                'slow_count' => $queries->where('is_slow', true)->count(),
                'duplicate_count' => $queries->where('is_duplicate', true)->count(),
            ],
            timing_anchors: is_array($anchors) ? $anchors : null,
        );
    }

    /**
     * Build from live collected data (objects, not flattened arrays).
     * Used to set the response header without a cache round-trip.
     */
    public static function fromCollectedData(array $data): self
    {
        $queries = collect(data_get($data, 'queries.queries', []));
        $anchors = data_get($data, 'metadata.timing_anchors');

        return new self(
            auth_mode: (string) data_get($data, 'metadata.auth_mode', 'guest'),
            request: [
                'route_name' => (string) data_get($data, 'request.route_name', '-'),
                'controller_action' => (string) data_get($data, 'request.controller_action', '-'),
            ],
            profiler: [
                'total_wall_time' => (string) data_get($data, 'profiler.total_wall_time.formattedValue', '0ms'),
                'total_real_memory' => (string) data_get($data, 'profiler.total_real_memory.formattedValue', '0B'),
                'total_allocated_memory' => (string) data_get($data, 'profiler.total_allocated_memory.formattedValue', '0B'),
                'stages' => self::summarizeStages(data_get($data, 'profiler.stages', [])),
            ],
            queries: [
                'count' => $queries->count(),
                'slow_count' => $queries->where('is_slow', true)->count(),
                'duplicate_count' => $queries->where('is_duplicate', true)->count(),
            ],
            timing_anchors: is_array($anchors) ? $anchors : null,
        );
    }

    private static function summarizeStages(mixed $stages): array
    {
        return Collection::wrap($stages)
            ->map(function (mixed $stage): array {
                return [
                    'label' => (string) data_get($stage, 'label', '-'),
                    'duration' => (string) data_get($stage, 'wall_time.measurement.formattedValue', '0ms'),
                ];
            })
            ->values()
            ->all();
    }
}
