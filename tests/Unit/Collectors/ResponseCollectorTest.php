<?php

use Illuminate\Http\Response;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Collectors\ResponseCollector;
use NckRtl\Toolbar\Data\Configurations\ResponseConfig;
use NckRtl\Toolbar\Data\ResponseData;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    Toolbar::enable();
    $toolbar = new Toolbar;
    $toolbar->config->collectors([]);
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);
});

it('returns response data dto', function () {
    $response = new Response('<html><body>Test</body></html>', 200);
    $collector = new ResponseCollector;
    $manager = new CollectorManager(response: $response);

    $data = $collector->collectData($manager);

    expect($data)->toBeInstanceOf(ResponseData::class);
});

it('has correct key', function () {
    $collector = new ResponseCollector;

    expect($collector->key())->toBe('response');
});

it('has correct config class', function () {
    $collector = new ResponseCollector;

    expect($collector->configClass())->toBe(ResponseConfig::class);
});

it('collects response status code', function () {
    $response = new Response('OK', 200);
    $collector = new ResponseCollector;
    $manager = new CollectorManager(response: $response);

    $data = $collector->collectData($manager);

    expect($data->status_code)->toBe(200);
});

it('collects different status codes', function () {
    $response = new Response('Not Found', 404);
    $collector = new ResponseCollector;
    $manager = new CollectorManager(response: $response);

    $data = $collector->collectData($manager);

    expect($data->status_code)->toBe(404);
});

it('collects response headers', function () {
    $response = new Response('Test', 200, ['X-Custom-Header' => 'test-value']);
    $collector = new ResponseCollector;
    $manager = new CollectorManager(response: $response);

    $data = $collector->collectData($manager);

    expect($data->headers)->toBeArray();
    expect($data->headers)->toHaveKey('x-custom-header');
    expect($data->headers['x-custom-header'])->toContain('test-value');
});

it('collects content size', function () {
    $content = '<html><body>Test content here</body></html>';
    $response = new Response($content, 200);
    $collector = new ResponseCollector;
    $manager = new CollectorManager(response: $response);

    $data = $collector->collectData($manager);

    expect($data->size)->not->toBeNull();
    expect($data->size->value)->toBeGreaterThan(0);
});

it('formats size correctly', function () {
    $content = str_repeat('a', 1024); // 1KB
    $response = new Response($content, 200);
    $collector = new ResponseCollector;
    $manager = new CollectorManager(response: $response);

    $data = $collector->collectData($manager);

    expect($data->size->formattedValue)->toContain('KB');
});
