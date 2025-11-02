<?php

namespace NckRtl\Toolbar\Data\CollectorConfigurations;

use NckRtl\Toolbar\Enums\DataProvider;
use Spatie\LaravelData\Data;

class RequestConfurationData extends Data
{
    public function __construct(
        public bool $enabled = true,
        public ?DataProvider $provider = null,
    ) {
        if (! $this->enabled || ! $this->provider) {
            return;
        }

        // Only DataProvider::Telescope is supported
        if (! class_exists('Laravel\Telescope\Telescope')) {
            throw new \Exception('Request collector is configured to use the Telescope provider, but Telescope is not installed.');
        }
    }
}
