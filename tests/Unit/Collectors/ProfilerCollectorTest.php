<?php

use NckRtl\Toolbar\Collectors\ProfilerCollector;
use NckRtl\Toolbar\Data\Configurations\ProfilerConfig;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    // Reset profiler state
    Profiler::$requestCheckpoints = [];
    Profiler::$viewRenders = [];
    Profiler::$profileMarkers = [];

    Toolbar::enable();
    $toolbar = new Toolbar;
    $toolbar->config->collectors([]);
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);
});

it('has correct key', function () {
    $collector = new ProfilerCollector;

    expect($collector->key())->toBe('profiler');
});

it('has correct config class', function () {
    $collector = new ProfilerCollector;

    expect($collector->configClass())->toBe(ProfilerConfig::class);
});

it('implements collector interface', function () {
    $collector = new ProfilerCollector;

    expect($collector)->toBeInstanceOf(\NckRtl\Toolbar\Collectors\CollectorInterface::class);
});

it('extends base collector', function () {
    $collector = new ProfilerCollector;

    expect($collector)->toBeInstanceOf(\NckRtl\Toolbar\Collectors\Collector::class);
});

it('has default config', function () {
    $collector = new ProfilerCollector;

    expect($collector->config)->toBeInstanceOf(ProfilerConfig::class);
});

// Note: Full ProfilerCollector data collection tests are in integration tests
// because they require a real request lifecycle with all checkpoints recorded
// by middleware (MiddlewareStart, MiddlewareEnd) in the correct sequence.
