<?php

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Collectors\LaravelCollector;
use NckRtl\Toolbar\Data\Configurations\LaravelConfig;
use NckRtl\Toolbar\Data\LaravelData;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    Toolbar::enable();
    $toolbar = new Toolbar;
    $toolbar->config->collectors([]);
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);
});

it('returns laravel data dto', function () {
    $collector = new LaravelCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data)->toBeInstanceOf(LaravelData::class);
});

it('has correct key', function () {
    $collector = new LaravelCollector;

    expect($collector->key())->toBe('laravel');
});

it('has correct config class', function () {
    $collector = new LaravelCollector;

    expect($collector->configClass())->toBe(LaravelConfig::class);
});

it('collects laravel version', function () {
    $collector = new LaravelCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->version)->toBe(app()->version());
});

it('collects environment', function () {
    $collector = new LaravelCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->environment)->toBe(app()->environment());
});

it('collects timezone', function () {
    $collector = new LaravelCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->timezone)->toBe(config('app.timezone'));
});

it('collects locale', function () {
    $collector = new LaravelCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->locale)->toBe(config('app.locale'));
});

it('collects debug mode', function () {
    // Set a known debug value
    config(['app.debug' => true]);

    $collector = new LaravelCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    // config() returns '1' or true depending on how it was set
    expect($data->debug)->toBeTruthy();
});

it('respects config field toggles for version', function () {
    $config = new LaravelConfig(version: false);
    $collector = new LaravelCollector(config: $config);
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->version)->toBeNull();
});

it('respects config field toggles for environment', function () {
    $config = new LaravelConfig(environment: false);
    $collector = new LaravelCollector(config: $config);
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->environment)->toBeNull();
});

it('respects config field toggles for timezone', function () {
    $config = new LaravelConfig(timezone: false);
    $collector = new LaravelCollector(config: $config);
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->timezone)->toBeNull();
});

it('respects config field toggles for locale', function () {
    $config = new LaravelConfig(locale: false);
    $collector = new LaravelCollector(config: $config);
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->locale)->toBeNull();
});

it('respects config field toggles for debug', function () {
    $config = new LaravelConfig(debug: false);
    $collector = new LaravelCollector(config: $config);
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->debug)->toBeNull();
});
