<?php

namespace NckRtl\Toolbar\Data\Tools;

use Spatie\LaravelData\Data;

class TimingsTool extends Data implements ToolInterface
{
    public function __construct(

    ) {}

    public function component(): string
    {
        return 'Timings';
    }
}
