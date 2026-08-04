<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Data\Tools;

use Spatie\LaravelData\Data;

class OrbitTool extends Data implements ToolInterface
{
    /**
     * @param  array<string, string>  $process_urls  Optional UI links keyed by Orbit process key.
     */
    public function __construct(
        public string $gateway_url = 'https://gateway.orbit',
        public array $process_urls = [],
    ) {}

    public function component(): string
    {
        return 'Orbit';
    }
}
