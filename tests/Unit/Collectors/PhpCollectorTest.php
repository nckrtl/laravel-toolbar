<?php

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Collectors\PhpCollector;
use NckRtl\Toolbar\Data\Configurations\PhpConfig;
use NckRtl\Toolbar\Data\PhpData;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    Toolbar::enable();
    $toolbar = new Toolbar;
    $toolbar->config->collectors([]);
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);
});

it('returns php data dto', function () {
    $collector = new PhpCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data)->toBeInstanceOf(PhpData::class);
});

it('has correct key', function () {
    $collector = new PhpCollector;

    expect($collector->key())->toBe('php');
});

it('has correct config class', function () {
    $collector = new PhpCollector;

    expect($collector->configClass())->toBe(PhpConfig::class);
});

it('collects php version', function () {
    $collector = new PhpCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->version)->toBe(phpversion());
});

it('collects memory limit', function () {
    $collector = new PhpCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->memory_limit)->toBe(ini_get('memory_limit'));
});

it('collects max execution time', function () {
    $collector = new PhpCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->max_execution_time)->toBe(ini_get('max_execution_time'));
});
