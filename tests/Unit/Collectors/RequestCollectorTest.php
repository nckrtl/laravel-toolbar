<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Collectors\RequestCollector;
use NckRtl\Toolbar\Data\Configurations\RequestConfig;
use NckRtl\Toolbar\Data\RequestData;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    Toolbar::enable();
    $toolbar = new Toolbar;
    $toolbar->config->collectors([]);
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);
});

it('returns request data dto', function () {
    $collector = new RequestCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data)->toBeInstanceOf(RequestData::class);
});

it('has correct key', function () {
    $collector = new RequestCollector;

    expect($collector->key())->toBe('request');
});

it('has correct config class', function () {
    $collector = new RequestCollector;

    expect($collector->configClass())->toBe(RequestConfig::class);
});

it('collects request method', function () {
    // Create and bind a request
    $request = Request::create('/test', 'POST');
    app()->instance('request', $request);

    $collector = new RequestCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->method)->toBe('POST');
});

it('collects request uri', function () {
    $request = Request::create('/api/users', 'GET');
    app()->instance('request', $request);

    $collector = new RequestCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->uri)->toBe('/api/users');
});

it('collects ip address', function () {
    $request = Request::create('/test', 'GET', [], [], [], ['REMOTE_ADDR' => '192.168.1.1']);
    app()->instance('request', $request);

    $collector = new RequestCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->ip_address)->toBe('192.168.1.1');
});

it('detects inertia requests', function () {
    $request = Request::create('/test', 'GET');
    $request->headers->set('X-Inertia', 'true');
    app()->instance('request', $request);

    $collector = new RequestCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->is_inertia)->toBeTrue();
});

it('detects non-inertia requests', function () {
    $request = Request::create('/test', 'GET');
    app()->instance('request', $request);

    $collector = new RequestCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->is_inertia)->toBeFalse();
});

it('collects controller action when route is matched', function () {
    Route::get('/test-route', function () {
        return 'test';
    })->name('test.route');

    $request = Request::create('/test-route', 'GET');
    $route = Route::getRoutes()->match($request);
    $request->setRouteResolver(fn () => $route);
    app()->instance('request', $request);

    $collector = new RequestCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->controller_action)->not->toBeNull();
});

it('returns dash for controller action when no route matched', function () {
    $request = Request::create('/unmatched-route', 'GET');
    app()->instance('request', $request);

    $collector = new RequestCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->controller_action)->toBe('-');
});

it('collects middleware stack when route is matched', function () {
    Route::middleware(['web'])->get('/middleware-test', function () {
        return 'test';
    });

    $request = Request::create('/middleware-test', 'GET');
    $route = Route::getRoutes()->match($request);
    $request->setRouteResolver(fn () => $route);
    app()->instance('request', $request);

    $collector = new RequestCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->middleware)->toBeArray();
});

it('returns empty middleware array when no route matched', function () {
    $request = Request::create('/no-middleware', 'GET');
    app()->instance('request', $request);

    $collector = new RequestCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->middleware)->toBeArray();
    expect($data->middleware)->toBeEmpty();
});
