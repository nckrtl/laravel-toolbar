<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class PhpData extends Data
{
    public function __construct(
        public string $version,
        public string $memory_limit,
        public string $max_execution_time,
    ) {}
}
