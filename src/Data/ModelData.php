<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Measurement;
use Spatie\LaravelData\Data;

class ModelData extends Data
{
    /** @var array<string, ModelSourceData> */
    public array $sources = [];

    public function __construct(
        public string $action,
        public string $model,
        public int $count,
        public ?Measurement $memory_used = null,
    ) {}
}
