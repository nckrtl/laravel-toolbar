<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class LaravelData extends Data
{
    public function __construct(
        public ?string $version = null,
        public ?string $environment = null,
        public ?string $timezone = null,
        public ?string $locale = null,
        public ?string $debug = null,
    ) {}
}
