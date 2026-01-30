<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class BootProfileData extends Data
{
    public function __construct(
        public array $before_autoload,
        public array $after_autoload,
        public array $after_bootstrap,
        public array $providers_registered,
        public array $providers_booted,
    ) {
    }
}
