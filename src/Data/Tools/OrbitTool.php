<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Data\Tools;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class OrbitTool extends Data implements ToolInterface
{
    /**
     * @param  array<string, OrbitProcessConfig>  $processes
     */
    public function __construct(
        public string $gateway_url = 'https://gateway.orbit',
        #[DataCollectionOf(OrbitProcessConfig::class)]
        public array $processes = [],
    ) {}

    public function component(): string
    {
        return 'Orbit';
    }
}
