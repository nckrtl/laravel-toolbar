<?php

namespace NckRtl\Toolbar\Data\Tools;

use Spatie\LaravelData\Data;

class RequestsTool extends Data implements ToolInterface
{
    public function component(): string
    {
        return 'Requests';
    }
}
