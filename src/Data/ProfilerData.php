<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Measurement;
use Spatie\LaravelData\Data;

class ProfilerData extends Data
{
    public function __construct(
        public Measurement $total_wall_time,
        public Measurement $total_real_memory,
        public Measurement $total_allocated_memory,
        public array $stages
    ) {}
}
