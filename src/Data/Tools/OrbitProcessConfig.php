<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Data\Tools;

use Spatie\LaravelData\Data;

class OrbitProcessConfig extends Data
{
    public function __construct(
        public string $label,
        public ?string $url = null,
    ) {}
}
