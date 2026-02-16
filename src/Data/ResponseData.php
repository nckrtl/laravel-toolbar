<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Measurement;
use Spatie\LaravelData\Data;

class ResponseData extends Data
{
    public function __construct(
        public int $status_code,
        public array $headers,
        public Measurement $size,
    ) {
    }
}
