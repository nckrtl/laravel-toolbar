<?php

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Collectors\ModelsCollector;
use NckRtl\Toolbar\Data\Configurations\ModelsConfig;
use NckRtl\Toolbar\Data\ModelData;
use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Measurement;
use NckRtl\Toolbar\Observers\ModelObserver;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    Profiler::$requestCheckpoints = [];
    Profiler::initialize();

    Toolbar::enable();
    $toolbar = new Toolbar;
    $toolbar->config->collectors([]);
    $toolbar->config->enableInConsole();

    // Add model observer to config
    $toolbar->config->observers([new ModelObserver]);

    app()->instance(Toolbar::class, $toolbar);
});

it('returns array of model data', function () {
    $collector = new ModelsCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data)->toBeArray();
});

it('has correct key', function () {
    $collector = new ModelsCollector;

    expect($collector->key())->toBe('models');
});

it('has correct config class', function () {
    $collector = new ModelsCollector;

    expect($collector->configClass())->toBe(ModelsConfig::class);
});

it('retrieves entries from model observer', function () {
    $toolbar = app(Toolbar::class);
    $modelObserver = $toolbar->config->getObserver(ModelObserver::class);

    // Simulate model hydration by adding entry directly
    $modelObserver->hydrationEntries['App\\Models\\User'] = new ModelData(
        action: 'retrieved',
        model: 'App\\Models\\User',
        count: 3,
        memory_used: new Measurement(2048, DataSizeUnit::BYTES),
    );

    $collector = new ModelsCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data)->toHaveKey('App\\Models\\User');
    expect($data['App\\Models\\User'])->toBeInstanceOf(ModelData::class);
});

it('returns empty array when no models hydrated', function () {
    $collector = new ModelsCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data)->toBeArray();
    expect($data)->toBeEmpty();
});

it('can be instantiated with custom config', function () {
    $config = new ModelsConfig(enabled: false);
    $collector = new ModelsCollector(config: $config);

    expect($collector->config->enabled)->toBeFalse();
});

it('has enabled config by default', function () {
    $collector = new ModelsCollector;

    expect($collector->config->enabled)->toBeTrue();
});

it('collects multiple model types', function () {
    $toolbar = app(Toolbar::class);
    $modelObserver = $toolbar->config->getObserver(ModelObserver::class);

    // Simulate multiple model hydrations
    $modelObserver->hydrationEntries['App\\Models\\User'] = new ModelData(
        action: 'retrieved',
        model: 'App\\Models\\User',
        count: 5,
        memory_used: new Measurement(2048, DataSizeUnit::BYTES),
    );

    $modelObserver->hydrationEntries['App\\Models\\Post'] = new ModelData(
        action: 'retrieved',
        model: 'App\\Models\\Post',
        count: 10,
        memory_used: new Measurement(4096, DataSizeUnit::BYTES),
    );

    $collector = new ModelsCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data)->toHaveCount(2);
    expect($data)->toHaveKey('App\\Models\\User');
    expect($data)->toHaveKey('App\\Models\\Post');
});

it('preserves model counts from observer', function () {
    $toolbar = app(Toolbar::class);
    $modelObserver = $toolbar->config->getObserver(ModelObserver::class);

    $modelObserver->hydrationEntries['App\\Models\\User'] = new ModelData(
        action: 'retrieved',
        model: 'App\\Models\\User',
        count: 42,
        memory_used: new Measurement(2048, DataSizeUnit::BYTES),
    );

    $collector = new ModelsCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data['App\\Models\\User']->count)->toBe(42);
});

it('preserves memory usage from observer', function () {
    $toolbar = app(Toolbar::class);
    $modelObserver = $toolbar->config->getObserver(ModelObserver::class);

    $modelObserver->hydrationEntries['App\\Models\\User'] = new ModelData(
        action: 'retrieved',
        model: 'App\\Models\\User',
        count: 1,
        memory_used: new Measurement(8192, DataSizeUnit::BYTES),
    );

    $collector = new ModelsCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data['App\\Models\\User']->memory_used->value)->toBe(8192);
});
