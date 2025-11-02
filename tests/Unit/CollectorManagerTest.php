<?php

use Illuminate\Support\Facades\Cache;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Collectors\LaravelCollector;
use NckRtl\Toolbar\Collectors\PhpCollector;
use NckRtl\Toolbar\Tests\Helpers\MockResponse;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    Toolbar::enable();

    $toolbar = new Toolbar;
    $toolbar->config->collectors([]);
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);
});

it('generates unique request id', function () {
    $manager1 = new CollectorManager;
    $manager2 = new CollectorManager;

    expect($manager1->id)->not->toBe($manager2->id);
    expect($manager1->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});

it('returns metadata when no collectors enabled', function () {
    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([]);

    $manager = new CollectorManager;
    $data = $manager->collectData();

    expect($data)->toHaveKey('metadata');
    expect($data['metadata'])->toHaveKey('id');
    expect($data['metadata'])->toHaveKey('timestamp');
    expect($data['metadata'])->toHaveKey('collectors');
    expect($data['metadata']['collectors'])->toContain('No collectors enabled');
    expect($data['metadata'])->toHaveKey('wall_time');
});

it('collects data from all enabled collectors', function () {
    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        new PhpCollector,
        new LaravelCollector,
    ]);

    $manager = new CollectorManager;
    $data = $manager->collectData();

    expect($data)->toHaveKey('php');
    expect($data)->toHaveKey('laravel');
    expect($data['php'])->not->toBeNull();
    expect($data['laravel'])->not->toBeNull();
});

it('skips disabled collectors', function () {
    $phpCollector = new PhpCollector;
    $phpCollector->config->enabled = false;

    $laravelCollector = new LaravelCollector;

    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        $phpCollector,
        $laravelCollector,
    ]);

    $manager = new CollectorManager;
    $data = $manager->collectData();

    expect($data)->not->toHaveKey('php');
    expect($data)->toHaveKey('laravel');
});

it('tracks wall time per collector', function () {
    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        new PhpCollector,
    ]);

    $manager = new CollectorManager;
    $data = $manager->collectData();

    expect($data)->toHaveKey('metadata');
    expect($data['metadata'])->toHaveKey('wall_time');
    expect($data['metadata']['wall_time'])->toHaveKey('collectors');
    expect($data['metadata']['wall_time']['collectors'])->toHaveKey('php');
    expect($data['metadata']['wall_time']['collectors']['php'])->toHaveKey('duration');
});

it('includes debug metadata when debug mode enabled', function () {
    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        new PhpCollector,
    ]);
    $toolbar->config->debug(true);

    $manager = new CollectorManager;
    $data = $manager->collectData();

    expect($data['metadata'])->toHaveKey('debug');
    expect($data['metadata']['debug'])->toBeTrue();
    expect($data['metadata'])->toHaveKey('timestamp');
    expect($data['metadata']['wall_time'])->toHaveKey('total');
});

it('does not include debug metadata when debug mode disabled', function () {
    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        new PhpCollector,
    ]);
    $toolbar->config->debug(false);

    $manager = new CollectorManager;
    $data = $manager->collectData();

    expect($data['metadata'])->not->toHaveKey('debug');
});

it('caches data for requests', function () {
    Cache::flush();

    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        new PhpCollector,
    ]);

    $manager = new CollectorManager;
    $data = $manager->collectData();

    $cachedData = Cache::get('laravel-toolbar-request-data-'.$manager->id);

    expect($cachedData)->not->toBeNull();
    expect($cachedData)->toHaveKey('php');
    expect($cachedData['metadata']['id'])->toBe($manager->id);
});

it('caches data with mcp request id when provided', function () {
    Cache::flush();

    $mcpId = 'test-mcp-id-123';

    // Mock the request with X-MCP-ID header
    $request = request();
    $request->headers->set('X-MCP-ID', $mcpId);

    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        new PhpCollector,
    ]);

    $manager = new CollectorManager;
    $data = $manager->collectData();

    $cachedData = Cache::get('laravel-toolbar-request-data-'.$mcpId);

    expect($cachedData)->not->toBeNull();
    expect($cachedData)->toHaveKey('php');

    // Clean up
    $request->headers->remove('X-MCP-ID');
});

it('includes layout configuration in data', function () {
    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        new PhpCollector,
    ]);

    $manager = new CollectorManager;
    $data = $manager->collectData();

    expect($data)->toHaveKey('layout');
});

it('accepts response in constructor', function () {
    $response = MockResponse::make('<html><body></body></html>');

    $manager = new CollectorManager(response: $response);

    expect($manager->response)->toBe($response);
});
