<?php

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Collectors\VueCollector;
use NckRtl\Toolbar\Data\Configurations\VueConfig;
use NckRtl\Toolbar\Data\VueData;
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

it('returns vue data dto', function () {
    $collector = new VueCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data)->toBeInstanceOf(VueData::class);
});

it('has correct key', function () {
    $collector = new VueCollector;

    expect($collector->key())->toBe('vue');
});

it('has correct config class', function () {
    $collector = new VueCollector;

    expect($collector->configClass())->toBe(VueConfig::class);
});

it('returns null or string version depending on vue installation', function () {
    $collector = new VueCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    // In test environment, Vue may not be installed
    // The important thing is it doesn't crash and returns VueData with valid type
    expect($data->version === null || is_string($data->version))->toBeTrue();
});

it('returns null or string editor url depending on package.json', function () {
    $collector = new VueCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    // In test environment, package.json may not exist or may not have vue
    // The important thing is it doesn't crash and returns VueData with valid type
    expect($data->version_editor_url === null || is_string($data->version_editor_url))->toBeTrue();
});

it('can be instantiated with custom config', function () {
    $config = new VueConfig(enabled: false);
    $collector = new VueCollector(config: $config);

    expect($collector->config->enabled)->toBeFalse();
});

it('has enabled config by default', function () {
    $collector = new VueCollector;

    expect($collector->config->enabled)->toBeTrue();
});
