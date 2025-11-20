<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class ModelData extends Data
{
    public function __construct(
        public string $action,
        public string $model,
        public int $count,
    ) {}
}
