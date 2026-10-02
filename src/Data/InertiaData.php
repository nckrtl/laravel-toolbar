<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class InertiaData extends Data
{
    public function __construct(
        public ?string $version = null,
        /** Per top-level prop: shared, type, defer_group, loaded, source {file, line}. */
        public ?array $props = null,
        public ?array $render_source = null,
        public ?string $component_path = null,
    ) {}
}
