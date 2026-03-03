<?php

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use NckRtl\Toolbar\Observers\RequestObserver;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    // Reset profiler checkpoints
    Profiler::$requestCheckpoints = [];
    Profiler::initialize();

    // Setup toolbar
    Toolbar::enable();
    $toolbar = new Toolbar;
    $toolbar->config->collectors([]);
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);

    // Mock routes needed for asset generation
    Route::get('/_toolbar/assets/{asset}', fn () => '')->name('toolbar.assets');
});

it('listens to RequestHandled event and injects toolbar', function () {
    $observer = new RequestObserver;

    $request = Request::create('/', 'GET');
    $response = new Response('<html><body></body></html>');

    // Dispatch the event
    event(new RequestHandled($request, $response));

    // Verify the observer handled the event by checking injection result
    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
});

it('resets profiler state after handling request', function () {
    $observer = new RequestObserver;

    $request = Request::create('/', 'GET');
    $response = new Response('<html><body></body></html>');

    event(new RequestHandled($request, $response));

    // State should be cleared after request handling (critical for Octane)
    expect(Profiler::$requestCheckpoints)->toBeEmpty();
});

it('injects toolbar into response', function () {
    $observer = new RequestObserver;

    $request = Request::create('/', 'GET');
    $response = new Response('<html><body></body></html>');

    event(new RequestHandled($request, $response));

    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
});
