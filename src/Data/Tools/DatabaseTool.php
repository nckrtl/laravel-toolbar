<?php

namespace NckRtl\Toolbar\Data\Tools;

use Spatie\LaravelData\Data;

class DatabaseTool extends Data implements ToolInterface
{
    public function __construct(

    ) {
    }

    public function component(): string
    {
        return 'Database';
    }
}
