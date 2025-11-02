<?php

namespace NckRtl\Toolbar\Data\Tools;

use Spatie\LaravelData\Data;

class BreakpointIndicatorTool extends Data implements ToolInterface
{
    public function __construct(
        public bool $show_pixels = true,
    ) {}

    public function component(): string
    {
        return 'BreakpointIndicator';
    }
}
