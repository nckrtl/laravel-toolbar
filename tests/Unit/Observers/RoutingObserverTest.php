<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Events\Routing;
use Illuminate\Support\Facades\Route;
use NckRtl\Toolbar\Enums\RequestCheckpointId;
use NckRtl\Toolbar\Observers\RoutingObserver;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;

beforeEach(function () {
    // Reset profiler checkpoints
    Profiler::$requestCheckpoints = [];
    Profiler::initialize();
});

it('records BEFORE_ROUTING checkpoint on Routing event', function () {
    $observer = new RoutingObserver;

    event(new Routing(Request::create('/', 'GET')));

    expect(Profiler::$requestCheckpoints)->toHaveKey(RequestCheckpointId::BEFORE_ROUTING->value);
});

it('records AFTER_ROUTING checkpoint on RouteMatched event', function () {
    $observer = new RoutingObserver;

    // Create a simple route and match it
    Route::get('/test', fn () => 'test')->name('test.route');
    $request = Request::create('/test', 'GET');
    $route = Route::getRoutes()->match($request);

    event(new RouteMatched($route, $request));

    expect(Profiler::$requestCheckpoints)->toHaveKey(RequestCheckpointId::AFTER_ROUTING->value);
});

it('listens to Routing event', function () {
    $observer = new RoutingObserver;

    $request = Request::create('/', 'GET');

    event(new Routing($request));

    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_ROUTING);

    expect($checkpoint)->not->toBeNull();
});

it('listens to RouteMatched event', function () {
    $observer = new RoutingObserver;

    Route::get('/matched', fn () => 'matched');
    $request = Request::create('/matched', 'GET');
    $route = Route::getRoutes()->match($request);

    event(new RouteMatched($route, $request));

    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::AFTER_ROUTING);

    expect($checkpoint)->not->toBeNull();
});
