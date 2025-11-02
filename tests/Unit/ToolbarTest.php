<?php

use NckRtl\Toolbar\Data\ToolbarConfig;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    // Reset the static enabled state before each test
    Toolbar::enable();
});

afterEach(function () {
    // Ensure we clean up static state
    Toolbar::enable();
});

it('can be disabled via static method', function () {
    expect(Toolbar::$enabled)->toBeTrue();

    Toolbar::disable();

    expect(Toolbar::$enabled)->toBeFalse();
});

it('can be enabled via static method', function () {
    Toolbar::disable();
    expect(Toolbar::$enabled)->toBeFalse();

    Toolbar::enable();

    expect(Toolbar::$enabled)->toBeTrue();
});

it('is disabled when running in console by default', function () {
    $toolbar = new Toolbar;
    $toolbar->config->enableInConsole(false);
    app()->instance(Toolbar::class, $toolbar);

    // We're running in console during tests
    expect(app()->runningInConsole())->toBeTrue();
    expect(Toolbar::isEnabled())->toBeFalse();
});

it('can be enabled in console mode', function () {
    $toolbar = new Toolbar;
    $toolbar->config->enableInConsole(true);
    app()->instance(Toolbar::class, $toolbar);

    expect(Toolbar::isEnabled())->toBeTrue();
});

it('reports disabled when static property is false', function () {
    $toolbar = new Toolbar;
    $toolbar->config->enableInConsole(true);
    app()->instance(Toolbar::class, $toolbar);

    Toolbar::disable();

    expect(Toolbar::isEnabled())->toBeFalse();
});

it('detects telescope installation', function () {
    $toolbar = new Toolbar;

    // Telescope is not installed in test environment
    expect($toolbar->telescopeIsInstalled())->toBeFalse();
});

it('initializes with toolbar config', function () {
    $toolbar = new Toolbar;

    expect($toolbar->config)->toBeInstanceOf(ToolbarConfig::class);
});

it('has empty collectors array by default', function () {
    $toolbar = new Toolbar;

    expect($toolbar->collectors)->toBeArray();
    expect($toolbar->collectors)->toBeEmpty();
});
