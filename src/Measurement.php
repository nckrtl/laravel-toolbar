<?php

namespace NckRtl\Toolbar;

use NckRtl\Toolbar\Enums\Unit;

class Measurement
{
    public string $formattedValue;

    public function __construct(
        public int|float $value,
        public Unit $unit
    ) {
        $this->formatValue();
    }

    public function convertTo(Unit $unit): self
    {
        $this->value = $this->unit->convertValueTo($this->value, $unit);
        $this->unit = $unit;

        $this->formatValue($unit);

        return $this;
    }

    public function decimals(int $decimals): self
    {
        $this->formattedValue = round($this->value, $decimals, PHP_ROUND_HALF_DOWN).$this->unit->abbreviation();

        return $this;
    }

    public function formatValue(?Unit $formatToUnit = null): self
    {
        $this->formattedValue = $this->unit->formatMaxFractionDigits(2, $this->value, $formatToUnit);

        return $this;
    }
}
