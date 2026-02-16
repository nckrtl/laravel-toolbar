<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;

class QueriesData extends Data
{
    public function __construct(
        public float $totalTime,
        public float $totalTimeFilteredQueries,
        public array $databases,
        public array $connections,
        public array $drivers,
        public array $queries,
    ) {
    }
}
