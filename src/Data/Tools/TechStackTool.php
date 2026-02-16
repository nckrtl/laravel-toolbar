<?php

namespace NckRtl\Toolbar\Data\Tools;

use Spatie\LaravelData\Data;

class TechStackTool extends Data implements ToolInterface
{
    public function __construct(
        public bool $laravel = true,
        public bool $vue = false,
        public bool $php = true,
        public bool $inertia = false,
        public bool $tailwind = false,
    ) {
    }

    public function component(): string
    {
        return 'TechStack';
    }
}
