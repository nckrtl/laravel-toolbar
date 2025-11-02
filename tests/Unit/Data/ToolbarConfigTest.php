<?php

use NckRtl\Toolbar\Collectors\CollectorInterface;
use NckRtl\Toolbar\Collectors\LaravelCollector;
use NckRtl\Toolbar\Collectors\PhpCollector;
use NckRtl\Toolbar\Data\ToolbarConfig;
use NckRtl\Toolbar\Observers\QueryObserver;
use NckRtl\Toolbar\Observers\RequestObserver;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    Toolbar::enable();
});

afterEach(function () {
    Toolbar::enable();
});

it('can be instantiated', function () {
    $config = new ToolbarConfig;

    expect($config)->toBeInstanceOf(ToolbarConfig::class);
});

it('has debug mode disabled by default', function () {
    $config = new ToolbarConfig;

    expect($config->debug)->toBeFalse();
});

it('enables debug mode', function () {
    $config = new ToolbarConfig;
    $config->debug(true);

    expect($config->debug)->toBeTrue();
});

it('disables debug mode', function () {
    $config = new ToolbarConfig;
    $config->debug(true);
    $config->debug(false);

    expect($config->debug)->toBeFalse();
});

it('toggles debug mode when called without argument', function () {
    $config = new ToolbarConfig;

    expect($config->debug)->toBeFalse();

    $config->debug();
    expect($config->debug)->toBeTrue();

    $config->debug();
    expect($config->debug)->toBeFalse();
});

it('returns self when setting debug for chaining', function () {
    $config = new ToolbarConfig;

    $result = $config->debug(true);

    expect($result)->toBe($config);
});

it('sets observers', function () {
    $config = new ToolbarConfig;
    $observers = [new QueryObserver];

    $config->observers($observers);

    expect($config->observers)->toBe($observers);
});

it('returns self when setting observers for chaining', function () {
    $config = new ToolbarConfig;

    $result = $config->observers([]);

    expect($result)->toBe($config);
});

it('retrieves observer by class', function () {
    $config = new ToolbarConfig;
    $queryObserver = new QueryObserver;
    $config->observers([$queryObserver]);

    $retrieved = $config->getObserver(QueryObserver::class);

    expect($retrieved)->toBe($queryObserver);
});

it('returns null when observer not found', function () {
    $config = new ToolbarConfig;
    $config->observers([new QueryObserver]);

    $retrieved = $config->getObserver(RequestObserver::class);

    expect($retrieved)->toBeNull();
});

it('sets collectors', function () {
    $config = new ToolbarConfig;
    $collectors = [new PhpCollector];

    $config->collectors($collectors);

    expect($config->collectors)->toBe($collectors);
});

it('returns self when setting collectors for chaining', function () {
    $config = new ToolbarConfig;

    $result = $config->collectors([new PhpCollector]);

    expect($result)->toBe($config);
});

it('validates collector implements interface', function () {
    $config = new ToolbarConfig;

    // Using a real collector that implements the interface
    $config->collectors([new PhpCollector]);

    expect($config->collectors[0])->toBeInstanceOf(CollectorInterface::class);
});

it('throws when collector does not implement interface', function () {
    $config = new ToolbarConfig;

    // Create a simple object that doesn't implement CollectorInterface
    $invalidCollector = new stdClass;

    $config->collectors([$invalidCollector]);
})->throws(Error::class);

it('filters enabled collectors', function () {
    $config = new ToolbarConfig;

    $enabledCollector = new PhpCollector;

    $disabledCollector = new LaravelCollector;
    $disabledCollector->config->enabled = false;

    $config->collectors([$enabledCollector, $disabledCollector]);

    $enabled = $config->enabledCollectors();

    expect($enabled)->toHaveCount(1);
    expect($enabled[0])->toBe($enabledCollector);
});

it('returns all collectors when all are enabled', function () {
    $config = new ToolbarConfig;

    $collector1 = new PhpCollector;
    $collector2 = new LaravelCollector;

    $config->collectors([$collector1, $collector2]);

    $enabled = $config->enabledCollectors();

    expect($enabled)->toHaveCount(2);
});

it('enables toolbar via config', function () {
    Toolbar::disable();
    expect(Toolbar::$enabled)->toBeFalse();

    $config = new ToolbarConfig;
    $config->enable();

    expect(Toolbar::$enabled)->toBeTrue();
});

it('disables toolbar via config', function () {
    Toolbar::enable();
    expect(Toolbar::$enabled)->toBeTrue();

    $config = new ToolbarConfig;
    $config->disable();

    expect(Toolbar::$enabled)->toBeFalse();
});

it('has console mode disabled by default', function () {
    $config = new ToolbarConfig;

    expect($config->enabledInConsole)->toBeFalse();
});

it('enables console mode', function () {
    $config = new ToolbarConfig;

    $config->enableInConsole(true);

    expect($config->enabledInConsole)->toBeTrue();
});

it('disables console mode', function () {
    $config = new ToolbarConfig;
    $config->enableInConsole(true);

    $config->enableInConsole(false);

    expect($config->enabledInConsole)->toBeFalse();
});

it('returns self when enabling console mode for chaining', function () {
    $config = new ToolbarConfig;

    $result = $config->enableInConsole();

    expect($result)->toBe($config);
});

it('updates collector config', function () {
    $config = new ToolbarConfig;
    $collector = new PhpCollector;
    $config->collectors([$collector]);

    $newCollectorConfig = new \NckRtl\Toolbar\Data\Configurations\PhpConfig;
    $newCollectorConfig->enabled = false;

    $config->updateCollectorConfig(PhpCollector::class, $newCollectorConfig);

    expect($config->collectors[0]->config->enabled)->toBeFalse();
});

it('throws exception when updating non-existent collector config', function () {
    $config = new ToolbarConfig;
    $config->collectors([new PhpCollector]);

    $newConfig = new \NckRtl\Toolbar\Data\Configurations\LaravelConfig;

    expect(fn () => $config->updateCollectorConfig(LaravelCollector::class, $newConfig))->toThrow(\Exception::class);
});

it('has layout configuration', function () {
    $config = new ToolbarConfig;

    expect($config->layout)->toBeInstanceOf(\NckRtl\Toolbar\Data\Layout\LayoutConfig::class);
});

it('sets empty collectors when empty array passed', function () {
    $config = new ToolbarConfig;
    $config->collectors([new PhpCollector]);

    $config->collectors([]);

    expect($config->collectors)->toBeEmpty();
});

it('has default collectors on initialization', function () {
    $config = new ToolbarConfig;

    expect($config->collectors)->not->toBeEmpty();
});

it('has default observers on initialization', function () {
    $config = new ToolbarConfig;

    expect($config->observers)->not->toBeEmpty();
});
