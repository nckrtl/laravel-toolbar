<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class LaravelData extends Data
{
    public function __construct(
        public ?string $version = null,
        public ?string $version_editor_url = null,
        public ?string $environment = null,
        public ?string $environment_editor_url = null,
        public ?string $host = null,
        public ?string $timezone = null,
        public ?string $timezone_editor_url = null,
        public ?string $locale = null,
        public ?string $locale_editor_url = null,
        public ?string $debug = null,
        public ?string $debug_editor_url = null,
    ) {
    }
}
