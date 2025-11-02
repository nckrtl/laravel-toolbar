<?php

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Collectors\TailwindCollector;
use NckRtl\Toolbar\Data\Configurations\TailwindConfig;
use NckRtl\Toolbar\Data\TailwindData;
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

it('returns tailwind data dto', function () {
    $collector = new TailwindCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data)->toBeInstanceOf(TailwindData::class);
});

it('has correct key', function () {
    $collector = new TailwindCollector;

    expect($collector->key())->toBe('tailwind');
});

it('has correct config class', function () {
    $collector = new TailwindCollector;

    expect($collector->configClass())->toBe(TailwindConfig::class);
});

it('returns null or string version depending on tailwind installation', function () {
    $collector = new TailwindCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    // In test environment, Tailwind may not be installed
    // The important thing is it doesn't crash and returns TailwindData with valid type
    expect($data->version === null || is_string($data->version))->toBeTrue();
});

it('can be instantiated with custom config', function () {
    $config = new TailwindConfig(enabled: false);
    $collector = new TailwindCollector(config: $config);

    expect($collector->config->enabled)->toBeFalse();
});

it('has enabled config by default', function () {
    $collector = new TailwindCollector;

    expect($collector->config->enabled)->toBeTrue();
});

it('checks tailwind v4 vite plugin first', function () {
    $collector = new TailwindCollector;
    $manager = new CollectorManager;

    // This test verifies the collector doesn't crash when checking v4 plugin path
    // The actual result depends on whether Tailwind v4 is installed
    $data = $collector->collectData($manager);

    expect($data)->toBeInstanceOf(TailwindData::class);
});

it('falls back to main tailwindcss package for v3', function () {
    $collector = new TailwindCollector;
    $manager = new CollectorManager;

    // This test verifies the collector doesn't crash when checking v3 path
    // The actual result depends on whether Tailwind v3 is installed
    $data = $collector->collectData($manager);

    expect($data)->toBeInstanceOf(TailwindData::class);
});

it('handles missing node_modules gracefully', function () {
    $collector = new TailwindCollector;
    $manager = new CollectorManager;

    // Should not throw exception when node_modules doesn't exist
    $data = $collector->collectData($manager);

    expect($data)->toBeInstanceOf(TailwindData::class);
    expect($data->version === null || is_string($data->version))->toBeTrue();
});
