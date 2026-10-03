<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class PhpData extends Data
{
    public function __construct(
        public string $version,
        public string $memory_limit,
        public string $max_execution_time,
        public ?string $sapi = null,
        /** Selected ini settings, by name. */
        public ?array $settings = null,
        public ?array $opcache = null,
        public ?array $extensions = null,
        /** PHP-FPM pool status and settings; null outside PHP-FPM. */
        public ?array $fpm = null,
    ) {}
}
