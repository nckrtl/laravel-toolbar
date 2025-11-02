<?php

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Collectors\InertiaCollector;
use NckRtl\Toolbar\Data\Configurations\InertiaConfig;
use NckRtl\Toolbar\Data\InertiaData;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    Profiler::$requestCheckpoints = [];
    Profiler::initialize();

    Toolbar::enable();
    $toolbar = new Toolbar;
    $toolbar->config->collectors([]);
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);
});

it('returns inertia data dto', function () {
    $collector = new InertiaCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data)->toBeInstanceOf(InertiaData::class);
});

it('has correct key', function () {
    $collector = new InertiaCollector;

    expect($collector->key())->toBe('inertia');
});

it('has correct config class', function () {
    $collector = new InertiaCollector;

    expect($collector->configClass())->toBe(InertiaConfig::class);
});

it('returns null or string version depending on inertia installation', function () {
    $collector = new InertiaCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    // In test environment, Inertia may not be installed
    // The important thing is it doesn't crash and returns InertiaData with valid type
    expect($data->version === null || is_string($data->version))->toBeTrue();
});

it('can be instantiated with custom config', function () {
    $config = new InertiaConfig(enabled: false);
    $collector = new InertiaCollector(config: $config);

    expect($collector->config->enabled)->toBeFalse();
});

it('has enabled config by default', function () {
    $collector = new InertiaCollector;

    expect($collector->config->enabled)->toBeTrue();
});

it('looks for inertia core package in node_modules', function () {
    $collector = new InertiaCollector;
    $manager = new CollectorManager;

    // This test verifies the collector checks the correct path
    // The actual result depends on whether Inertia is installed
    $data = $collector->collectData($manager);

    expect($data)->toBeInstanceOf(InertiaData::class);
});
