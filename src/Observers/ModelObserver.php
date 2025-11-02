<?php

namespace NckRtl\Toolbar\Observers;

use BackedEnum;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use NckRtl\Toolbar\Data\ModelData;
use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Measurement;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;

class ModelObserver
{
    private int $currentMemory = 0;

    public array $hydrationEntries = [];

    public function __construct(array $options = [])
    {
        $events = $options['events'] ?? 'eloquent.*';
        app('events')->listen($events, [$this, 'recordAction']);
    }

    /**
     * Reset all state for a new request.
     *
     * This is critical for long-running processes like Laravel Octane
     * where the same PHP process handles multiple requests.
     */
    public function reset(): void
    {
        $this->currentMemory = 0;
        $this->hydrationEntries = [];
    }

    public function recordAction($event, $data)
    {
        if ($this->currentMemory == 0) {
            $this->currentMemory = Profiler::getCurrentMemoryUsage()->value;
        }

        if (! Str::is('*retrieved*', $event)) {
            return;
        }

        $memoryBefore = $this->currentMemory;
        $memoryAfter = memory_get_usage();

        $this->recordHydrations($data, $memoryBefore, $memoryAfter);
    }

    /**
     * Record model hydrations.
     *
     * @param  array  $data
     * @return void
     */
    public function recordHydrations($data, $memoryBefore, $memoryAfter)
    {
        $modelClass = get_class($data['model'] ?? $data[0]);

        if (! isset($this->hydrationEntries[$modelClass])) {
            $this->hydrationEntries[$modelClass] = new ModelData(
                action: 'retrieved',
                model: $modelClass,
                count: 1,
                memory_used: new Measurement($memoryAfter - $memoryBefore, DataSizeUnit::BYTES),
            );
        } else {
            $model = $this->hydrationEntries[$modelClass];
            $model->memory_used->formatValue();
            $model->count++;
        }

        $this->currentMemory = $memoryAfter;
    }

    public function given($model)
    {
        if ($model instanceof Pivot && ! $model->incrementing) {
            $keys = [
                $model->getAttribute($model->getForeignKey()),
                $model->getAttribute($model->getRelatedKey()),
            ];
        } else {
            $keys = $model->getKey();
        }

        return get_class($model).':'.implode('_', array_map(function ($value) {
            return $value instanceof BackedEnum ? $value->value : $value;
        }, Arr::wrap($keys)));
    }
}
