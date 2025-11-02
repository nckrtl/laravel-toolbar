<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Enums\Unit;
use NckRtl\Toolbar\Measurement;
use Spatie\LaravelData\Data;

class RequestStagePropertyData extends Data
{
    public float $percentage = 0;

    public Measurement $measurement;

    public function __construct(
        float $value,
        Unit $unit
    ) {
        $this->measurement = new Measurement($value, $unit);
    }

    public function calculatePercentage(Measurement $total): void
    {
        if ($this->measurement->value > 0 && $total->value > 0) {
            $this->percentage = round(($this->measurement->value / $total->value) * 100, 2);
        }
    }

    public function convertTo(Unit $unit): self
    {
        $this->measurement->convertTo($unit);

        return $this;
    }
}
