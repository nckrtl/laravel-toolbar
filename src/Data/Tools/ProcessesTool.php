<?php

namespace NckRtl\Toolbar\Data\Tools;

use Spatie\LaravelData\Data;

class ProcessesTool extends Data implements ToolInterface
{
    public function component(): string
    {
        return 'Processes';
    }
}
