<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class TimingData extends Data
{
    public function __construct(
        public float $total_ms,
        public array $stages,
    ) {}
}
