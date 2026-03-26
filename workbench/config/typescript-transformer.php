<?php

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptCollector;
use Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptTransformer;
use Spatie\LaravelTypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\LaravelTypeScriptTransformer\Transformers\SpatieStateTransformer;
use Spatie\TypeScriptTransformer\Collectors\DefaultCollector;
use Spatie\TypeScriptTransformer\Collectors\EnumCollector;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;

return [
    'auto_discover_types' => [
        __DIR__.'/../../src/Data',
    ],

    'transformers' => [
        DataTypeScriptTransformer::class,
        SpatieStateTransformer::class,
        EnumTransformer::class,
        DtoTransformer::class,
    ],

    'collectors' => [
        DataTypeScriptCollector::class,
        DefaultCollector::class,
        EnumCollector::class,
    ],

    'output_file' => __DIR__.'/../../resources/js/types/generated.d.ts',

    'default_type_replacements' => [
        DateTime::class => 'string',
        DateTimeImmutable::class => 'string',
        Carbon\Carbon::class => 'string',
        CarbonImmutable::class => 'string',
    ],
];
