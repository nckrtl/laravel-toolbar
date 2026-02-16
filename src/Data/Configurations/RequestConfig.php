<?php

namespace NckRtl\Toolbar\Data\Configurations;

use NckRtl\Toolbar\Enums\DataProvider;
use Spatie\LaravelData\Data;

class RequestConfig extends Data implements CollectorConfig
{
    public function __construct(
        public bool $enabled = true,
        public ?DataProvider $dataProvider = null,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
