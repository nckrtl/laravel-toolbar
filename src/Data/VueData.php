<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class VueData extends Data
{
    public function __construct(
        public ?string $version = null,
        public ?string $version_editor_url = null,
        public ?string $devtools_version = null,
    ) {}
}
