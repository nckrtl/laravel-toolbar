<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Enums\TimeUnit;
use NckRtl\Toolbar\Measurement;
use Spatie\LaravelData\Data;

class RequestStageData extends Data
{
    public bool $recordedStart = true;

    public bool $recordedEnd = true;

    public RequestStagePropertyData $wall_time;

    public ?RequestStagePropertyData $memory_real_delta = null;

    public function __construct(
        public string $label,
        public ?RequestCheckpointData $start,
        public ?RequestCheckpointData $end,
        public string $color,
        public array $filesInvolved = []
    ) {

        if (! $this->start) {
            $this->recordedStart = false;
        }

        if (! $this->end) {
            $this->recordedEnd = false;
        }

        if (! $this->recordedStart || ! $this->recordedEnd) {
            $this->wall_time = new RequestStagePropertyData(0, TimeUnit::SECONDS);
            $this->memory_real_delta = new RequestStagePropertyData(0, DataSizeUnit::BYTES);

            return;
        }

        $this->calculateWallTime()->calculateMemoryDeltas();
    }

    public function calculateWallTime(): self
    {
        $this->wall_time = new RequestStagePropertyData(
            $this->end->time->value - $this->start->time->value,
            $this->end->time->unit
        )->convertTo(TimeUnit::MILLISECONDS);

        return $this;
    }

    public function calculateMemoryDeltas(): self
    {
        if (! $this->start->measureMemory || ! $this->end->measureMemory) {
            $this->memory_real_delta = new RequestStagePropertyData(0, DataSizeUnit::BYTES);

            return $this;
        }

        $this->memory_real_delta = new RequestStagePropertyData($this->end->memory_real->value - $this->start->memory_real->value, $this->end->memory_real->unit);

        return $this;
    }

    public function calculatePercentages(Measurement $totalWallTime, Measurement $totalRealMemory, Measurement $totalAllocatedMemory): self
    {
        $this->wall_time->calculatePercentage($totalWallTime);

        $this->memory_real_delta->calculatePercentage($totalRealMemory);

        return $this;
    }

    public function convertToPreferedUnits(): self
    {
        $this->wall_time->convertTo(TimeUnit::MILLISECONDS);
        $this->memory_real_delta->convertTo(DataSizeUnit::MEGABYTES);

        return $this;
    }
}
