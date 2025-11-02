<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Enums\TimeUnit;
use NckRtl\Toolbar\Measurement;
use Spatie\LaravelData\Data;

class RequestCheckpointData extends Data
{
    public function __construct(
        public ?Measurement $memory_real = null,
        public ?Measurement $time = null,
        public bool $measureMemory = true,
    ) {
        if ($this->measureMemory) {
            $this->memory_real = $this->memory_real ?? new Measurement(memory_get_usage(), DataSizeUnit::BYTES);
        }
        $this->time = $this->time ?? new Measurement(microtime(true), TimeUnit::SECONDS);
    }
}
