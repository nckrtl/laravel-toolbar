<?php

namespace NckRtl\Toolbar\Data\Tools;

use Spatie\LaravelData\Data;

class RequestTool extends Data implements ToolInterface
{
    public function __construct(
        public bool $show_status = true,
        public bool $url = true,
    ) {}

    public function component(): string
    {
        return 'Request';
    }
}
