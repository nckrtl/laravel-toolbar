<?php

namespace NckRtl\Toolbar;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Telescope\Telescope;
use NckRtl\Toolbar\Enums\TimeUnit;

class CollectorManager
{
    public string $id;

    public bool $telescopeIsInstalled;

    public Collection $telescopeEntries;

    public array $data = [];

    public function __construct(public Response|JsonResponse|null $response = null)
    {
        $this->id = Str::uuid();
    }

    /**
     * Get all collected data
     */
    public function collectData(): array
    {
        $toolbar = app(Toolbar::class);

        $startTime = microtime(true);

        if (empty($collectors = $toolbar->config->enabledCollectors())) {
            $time = microtime(true);

            return [
                'metadata' => [
                    'id' => $this->id,
                    'timestamp' => $time,
                    'collectors' => 'No collectors enabled in the toolbar configuration (toolbar.php)',
                    'wall_time' => [
                        'total' => new Measurement($startTime - $time, TimeUnit::SECONDS)->formattedValue,
                    ],
                ],
            ];
        }

        if ($toolbar->telescopeIsInstalled()) {
            $this->setTelescopeEntries();
        }

        foreach ($collectors as $collector) {
            $startCollector = microtime(true);

            $this->data[$collector->key()] = $collector->collectData(
                collectorManager: $this,
            );

            $endCollector = microtime(true);

            $this->data['metadata']['wall_time']['collectors'][$collector->key()]['duration'] = new Measurement($endCollector - $startCollector, TimeUnit::SECONDS)->formattedValue;
        }

        $this->data['metadata']['id'] = $this->id;

        if (app(Toolbar::class)->config->debug) {
            $endTime = microtime(true);

            $this->data['metadata']['debug'] = true;
            $this->data['metadata']['timestamp'] = $endTime;
            $this->data['metadata']['wall_time']['total'] = new Measurement($endTime - $startTime, TimeUnit::SECONDS)->formattedValue;
        }

        $mcpRequestId = request()->header('X-MCP-ID');
        Cache::put('laravel-toolbar-request-data-'.($mcpRequestId ?? $this->id), $this->data, 30);

        return $this->data;
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
