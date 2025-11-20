<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class VueData extends Data
{
    public function __construct(
        public string $version,
        public ?string $devtools_version = null,
    ) {}
}
