<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class QueryData extends Data
{
    public function __construct(
        public string $hash,
        public string $sql,
        public array $bindings,
        public float $duration,
        public string $connection,
        public string $driver,
        public string $file,
        public int $line,
        public bool $isDuplicate,
        public bool $isSlow,
        public float $percentage,
        public float $offset,
    ) {}
}
