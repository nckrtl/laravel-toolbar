<?php

namespace NckRtl\Toolbar\Data\Tools;

use Spatie\LaravelData\Data;

class AgentationTool extends Data implements ToolInterface
{
    public function component(): string
    {
        return 'Agentation';
    }
}
