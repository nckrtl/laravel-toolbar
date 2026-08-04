<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Data\Tools;

use Spatie\LaravelData\Data;

class OrbitTool extends Data implements ToolInterface
{
    public function __construct(
        public string $gateway_url = 'https://gateway.orbit',
    ) {}

    public function component(): string
    {
        return 'Orbit';
    }
}
