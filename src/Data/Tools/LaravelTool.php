<?php

namespace NckRtl\Toolbar\Data\Tools;

use Spatie\LaravelData\Data;

class LaravelTool extends Data implements ToolInterface
{
    public function __construct(
        public bool $show_version = true,
    ) {}

    public function component(): string
    {
        return 'Laravel';
    }
}
