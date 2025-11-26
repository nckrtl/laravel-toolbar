<?php

return [
    'auto_discover_types' => [
        __DIR__ . '/../../src/Data',
    ],

    'transformers' => [
        Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptTransformer::class,
        Spatie\LaravelTypeScriptTransformer\Transformers\SpatieStateTransformer::class,
        Spatie\TypeScriptTransformer\Transformers\EnumTransformer::class,
        Spatie\LaravelTypeScriptTransformer\Transformers\DtoTransformer::class,
    ],

    'collectors' => [
        Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptCollector::class,
        Spatie\TypeScriptTransformer\Collectors\DefaultCollector::class,
        Spatie\TypeScriptTransformer\Collectors\EnumCollector::class,
    ],

    'output_file' => __DIR__ . '/../../resources/js/types/generated.d.ts',

    'default_type_replacements' => [
        DateTime::class => 'string',
        DateTimeImmutable::class => 'string',
        Carbon\Carbon::class => 'string',
        Carbon\CarbonImmutable::class => 'string',
    ],
];
