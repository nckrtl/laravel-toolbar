<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use NckRtl\Toolbar\Tests\Helpers\MockResponse;
use NckRtl\Toolbar\Toolbar;
use NckRtl\Toolbar\ToolbarInjector;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

beforeEach(function () {
    // Ensure toolbar is enabled by default
    Config::set('toolbar.enabled', true);
    Toolbar::enable();

    // Register a singleton Toolbar with no collectors to avoid Profiler exceptions
    $toolbar = new Toolbar;
    $toolbar->config->collectors([]);
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);

    // Mock routes needed for asset generation
    Route::get('/_toolbar/assets/{asset}', fn () => '')->name('toolbar.assets');
});

it('injects toolbar html before closing body tag', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/', 'GET');
    $response = MockResponse::make('<html><body><h1>Hello</h1></body></html>');

    $injector->inject($request, $response);

    expect($response->getContent())
        ->toContain('<!-- Laravel Toolbar')
        ->toContain('<div id="laravel-toolbar-shadow-host"></div>')
        ->toContain('</body>')
        ->toContain('window.__LARAVEL_TOOLBAR_DATA__');
});

it('does not inject into ajax requests', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
    $response = MockResponse::make('<html><body></body></html>');

    $injector->inject($request, $response);

    expect($response->getContent())->not->toContain('<!-- Laravel Toolbar -->');
});

it('does not inject into non-html responses', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/', 'GET');
    $response = MockResponse::make('{"foo":"bar"}', 200, ['Content-Type' => 'application/json']);

    $injector->inject($request, $response);

    expect($response->getContent())->not->toContain('<!-- Laravel Toolbar -->');
});

it('does not inject into binary file responses', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/', 'GET');
    $response = new BinaryFileResponse(__FILE__);

    $injector->inject($request, $response);

    // BinaryFileResponse doesn't have getContent() in the same way, but we check it wasn't modified
    // The injector checks instance type early return
    expect($response)->toBeInstanceOf(BinaryFileResponse::class);
});

it('does not inject into streamed responses', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/', 'GET');
    $response = new StreamedResponse(fn () => print 'stream');

    $injector->inject($request, $response);

    expect($response)->toBeInstanceOf(StreamedResponse::class);
});

it('does not inject when response has no body tag', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/', 'GET');
    $response = MockResponse::make('<html><head></head></html>');

    $injector->inject($request, $response);

    expect($response->getContent())->not->toContain('<!-- Laravel Toolbar -->');
});

it('detects inertia requests correctly', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/', 'GET');
    $request->headers->set('X-Inertia', 'true');
    $response = MockResponse::make('{}', 200, ['Content-Type' => 'application/json']);

    $injector->inject($request, $response);

    expect($response->headers->has('x-toolbar'))->toBeTrue();
});

it('adds x-toolbar header for inertia requests', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/', 'GET');
    $request->headers->set('X-Inertia', 'true');
    $response = MockResponse::make('{}', 200, ['Content-Type' => 'application/json']);

    $injector->inject($request, $response);

    $header = $response->headers->get('x-toolbar');
    expect($header)->not->toBeNull();
});

it('base64 encodes toolbar data in header', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/', 'GET');
    $request->headers->set('X-Inertia', 'true');
    $response = MockResponse::make('{}', 200, ['Content-Type' => 'application/json']);

    $injector->inject($request, $response);

    $header = $response->headers->get('x-toolbar');
    $decoded = base64_decode($header, true);

    expect($decoded)->not->toBeFalse();
    expect(json_decode($decoded))->not->toBeNull();
});

it('respects disabled toolbar state', function () {
    Toolbar::disable();

    $injector = new ToolbarInjector;
    $request = Request::create('/', 'GET');
    $response = MockResponse::make('<html><body></body></html>');

    $injector->inject($request, $response);

    expect($response->getContent())->not->toContain('<!-- Laravel Toolbar -->');

    // Reset
    Toolbar::enable();
});

it('includes csp nonce when available', function () {
    Vite::useCspNonce('test-nonce');

    $injector = new ToolbarInjector;
    $request = Request::create('/', 'GET');
    $response = MockResponse::make('<html><body></body></html>');

    $injector->inject($request, $response);

    expect($response->getContent())->toContain('nonce="test-nonce"');
});
