<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Enums\TimeUnit;
use NckRtl\Toolbar\Measurement;
use Spatie\LaravelData\Data;

class ProfileMarkerData extends Data
{
    public function __construct(
        public string $label,
        public ?Measurement $memory_real = null,
        public ?Measurement $time = null,
    ) {
        $this->memory_real = $this->memory_real ?? new Measurement(memory_get_usage(), DataSizeUnit::BYTES);
        $this->time = $this->time ?? new Measurement(microtime(true), TimeUnit::SECONDS);
    }
}
