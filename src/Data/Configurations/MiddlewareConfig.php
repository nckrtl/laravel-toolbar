<?php

namespace NckRtl\Toolbar\Data\Configurations;

use Spatie\LaravelData\Data;

class MiddlewareConfig extends Data
{
    public function __construct(
        public array $prepend = [],
        public array $append = [],
        public bool $enabled = true,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
