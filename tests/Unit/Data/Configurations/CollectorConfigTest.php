<?php

use NckRtl\Toolbar\Data\Configurations\CollectorConfig;
use NckRtl\Toolbar\Data\Configurations\LaravelConfig;
use NckRtl\Toolbar\Data\Configurations\ModelsConfig;
use NckRtl\Toolbar\Data\Configurations\PhpConfig;
use NckRtl\Toolbar\Data\Configurations\ProfilerConfig;
use NckRtl\Toolbar\Data\Configurations\QueriesConfig;
use NckRtl\Toolbar\Data\Configurations\RequestConfig;
use NckRtl\Toolbar\Data\Configurations\ResponseConfig;
use NckRtl\Toolbar\Enums\DataProvider;

// CollectorConfig Interface Tests

it('CollectorConfig interface CollectorConfigTest isEnabled method', function () {
    $reflection = new ReflectionClass(CollectorConfig::class);

    expect($reflection->isInterface())->toBeTrue();
    expect($reflection->hasMethod('isEnabled'))->toBeTrue();
});

// LaravelConfig Tests

it('LaravelConfig implements CollectorConfig', function () {
    $config = new LaravelConfig;

    expect($config)->toBeInstanceOf(CollectorConfig::class);
});

it('LaravelConfig is enabled by default', function () {
    $config = new LaravelConfig;

    expect($config->enabled)->toBeTrue();
    expect($config->isEnabled())->toBeTrue();
});

it('LaravelConfig can be disabled', function () {
    $config = new LaravelConfig(enabled: false);

    expect($config->enabled)->toBeFalse();
    expect($config->isEnabled())->toBeFalse();
});

it('LaravelConfig has version field enabled by default', function () {
    $config = new LaravelConfig;

    expect($config->version)->toBeTrue();
});

it('LaravelConfig has environment field enabled by default', function () {
    $config = new LaravelConfig;

    expect($config->environment)->toBeTrue();
});

it('LaravelConfig has debug field enabled by default', function () {
    $config = new LaravelConfig;

    expect($config->debug)->toBeTrue();
});

it('LaravelConfig has timezone field enabled by default', function () {
    $config = new LaravelConfig;

    expect($config->timezone)->toBeTrue();
});

it('LaravelConfig has locale field enabled by default', function () {
    $config = new LaravelConfig;

    expect($config->locale)->toBeTrue();
});

it('LaravelConfig can disable individual fields', function () {
    $config = new LaravelConfig(
        version: false,
        environment: false,
        debug: false,
        timezone: false,
        locale: false,
    );

    expect($config->version)->toBeFalse();
    expect($config->environment)->toBeFalse();
    expect($config->debug)->toBeFalse();
    expect($config->timezone)->toBeFalse();
    expect($config->locale)->toBeFalse();
});

// PhpConfig Tests

it('PhpConfig implements CollectorConfig', function () {
    $config = new PhpConfig;

    expect($config)->toBeInstanceOf(CollectorConfig::class);
});

it('PhpConfig is enabled by default', function () {
    $config = new PhpConfig;

    expect($config->enabled)->toBeTrue();
    expect($config->isEnabled())->toBeTrue();
});

it('PhpConfig can be disabled', function () {
    $config = new PhpConfig(enabled: false);

    expect($config->enabled)->toBeFalse();
    expect($config->isEnabled())->toBeFalse();
});

// ProfilerConfig Tests

it('ProfilerConfig implements CollectorConfig', function () {
    $config = new ProfilerConfig;

    expect($config)->toBeInstanceOf(CollectorConfig::class);
});

it('ProfilerConfig is enabled by default', function () {
    $config = new ProfilerConfig;

    expect($config->enabled)->toBeTrue();
    expect($config->isEnabled())->toBeTrue();
});

it('ProfilerConfig can be disabled', function () {
    $config = new ProfilerConfig(enabled: false);

    expect($config->enabled)->toBeFalse();
    expect($config->isEnabled())->toBeFalse();
});

// QueriesConfig Tests

it('QueriesConfig implements CollectorConfig', function () {
    $config = new QueriesConfig;

    expect($config)->toBeInstanceOf(CollectorConfig::class);
});

it('QueriesConfig is enabled by default', function () {
    $config = new QueriesConfig;

    expect($config->enabled)->toBeTrue();
    expect($config->isEnabled())->toBeTrue();
});

it('QueriesConfig can be disabled', function () {
    $config = new QueriesConfig(enabled: false);

    expect($config->enabled)->toBeFalse();
    expect($config->isEnabled())->toBeFalse();
});

it('QueriesConfig shows session queries by default', function () {
    $config = new QueriesConfig;

    expect($config->showSessionQueries)->toBeTrue();
});

it('QueriesConfig can hide session queries', function () {
    $config = new QueriesConfig(showSessionQueries: false);

    expect($config->showSessionQueries)->toBeFalse();
});

// RequestConfig Tests

it('RequestConfig implements CollectorConfig', function () {
    $config = new RequestConfig;

    expect($config)->toBeInstanceOf(CollectorConfig::class);
});

it('RequestConfig is enabled by default', function () {
    $config = new RequestConfig;

    expect($config->enabled)->toBeTrue();
    expect($config->isEnabled())->toBeTrue();
});

it('RequestConfig can be disabled', function () {
    $config = new RequestConfig(enabled: false);

    expect($config->enabled)->toBeFalse();
    expect($config->isEnabled())->toBeFalse();
});

it('RequestConfig has null dataProvider by default', function () {
    $config = new RequestConfig;

    expect($config->dataProvider)->toBeNull();
});

it('RequestConfig can set dataProvider', function () {
    $config = new RequestConfig(dataProvider: DataProvider::Telescope);

    expect($config->dataProvider)->toBe(DataProvider::Telescope);
});

// ResponseConfig Tests

it('ResponseConfig implements CollectorConfig', function () {
    $config = new ResponseConfig;

    expect($config)->toBeInstanceOf(CollectorConfig::class);
});

it('ResponseConfig is enabled by default', function () {
    $config = new ResponseConfig;

    expect($config->enabled)->toBeTrue();
    expect($config->isEnabled())->toBeTrue();
});

it('ResponseConfig can be disabled', function () {
    $config = new ResponseConfig(enabled: false);

    expect($config->enabled)->toBeFalse();
    expect($config->isEnabled())->toBeFalse();
});

// ModelsConfig Tests

it('ModelsConfig implements CollectorConfig', function () {
    $config = new ModelsConfig;

    expect($config)->toBeInstanceOf(CollectorConfig::class);
});

it('ModelsConfig is enabled by default', function () {
    $config = new ModelsConfig;

    expect($config->enabled)->toBeTrue();
    expect($config->isEnabled())->toBeTrue();
});

it('ModelsConfig can be disabled', function () {
    $config = new ModelsConfig(enabled: false);

    expect($config->enabled)->toBeFalse();
    expect($config->isEnabled())->toBeFalse();
});
