<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class InertiaData extends Data
{
    public function __construct(
        public ?string $version = null,
    ) {}
}
