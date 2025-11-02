<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Enums\TimeUnit;
use NckRtl\Toolbar\Measurement;
use Spatie\LaravelData\Data;

class SubstageData extends Data
{
    public ?RequestStagePropertyData $wall_time = null;

    public ?RequestStagePropertyData $memory_real_delta = null;

    public function __construct(
        public string $label,
        public ProfileMarkerData $start,
        public ProfileMarkerData $end,
    ) {
        $this->calculateWallTime();
        $this->calculateMemoryDelta();
    }

    public function calculateWallTime(): self
    {
        $this->wall_time = new RequestStagePropertyData(
            $this->end->time->value - $this->start->time->value,
            $this->end->time->unit
        )->convertTo(TimeUnit::MILLISECONDS);

        return $this;
    }

    public function calculateMemoryDelta(): self
    {
        $this->memory_real_delta = new RequestStagePropertyData(
            $this->end->memory_real->value - $this->start->memory_real->value,
            DataSizeUnit::BYTES
        );

        return $this;
    }

    public function calculatePercentages(Measurement $totalWallTime, Measurement $totalRealMemory): self
    {
        $this->wall_time->calculatePercentage($totalWallTime);

        if ($totalRealMemory->value > 0) {
            $this->memory_real_delta->calculatePercentage($totalRealMemory);
        }

        return $this;
    }
}
