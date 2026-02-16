<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class TailwindData extends Data
{
    public function __construct(
        public ?string $version = null,
    ) {
    }
}
