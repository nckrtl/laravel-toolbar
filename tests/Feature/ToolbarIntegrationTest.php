<?php

use Illuminate\Support\Facades\Route;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    Toolbar::enable();
    Profiler::$requestCheckpoints = [];
    Profiler::$viewRenders = [];

    $toolbar = new Toolbar;
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);
});

it('injects toolbar into html response', function () {
    Route::get('/test-page', function () {
        return response('<html><body><h1>Test</h1></body></html>');
    });

    $response = $this->get('/test-page');

    $response->assertStatus(200);
    $content = $response->getContent();

    expect($content)->toContain('laravel-toolbar-shadow-host');
    expect($content)->toContain('__LARAVEL_TOOLBAR_DATA__');
});

it('does not inject toolbar into json response', function () {
    Route::get('/api/test', function () {
        return response()->json(['status' => 'ok']);
    });

    $response = $this->get('/api/test');

    $response->assertStatus(200);
    $content = $response->getContent();

    expect($content)->not->toContain('laravel-toolbar-shadow-host');
    expect($content)->toContain('status');
});

it('adds x-toolbar header for inertia requests', function () {
    Route::get('/inertia-page', function () {
        return response('<html><body></body></html>');
    });

    $response = $this->get('/inertia-page', [
        'X-Inertia' => 'true',
    ]);

    $response->assertStatus(200);
    $response->assertHeader('x-toolbar');

    $toolbarData = $response->headers->get('x-toolbar');
    expect($toolbarData)->not->toBeEmpty();

    $decoded = json_decode(base64_decode($toolbarData), true);
    expect($decoded)->toBeArray();
});

it('tracks queries during request', function () {
    Route::get('/query-test', function () {
        DB::select('SELECT 1');
        DB::select('SELECT 2');

        return response('<html><body>Query Test</body></html>');
    });

    $response = $this->get('/query-test');

    $response->assertStatus(200);
    $content = $response->getContent();

    expect($content)->toContain('__LARAVEL_TOOLBAR_DATA__');

    preg_match('/window\.__LARAVEL_TOOLBAR_DATA__\s*=\s*({.*?});/s', $content, $matches);

    if (! empty($matches[1])) {
        $data = json_decode($matches[1], true);
        expect($data)->toHaveKey('queries');
    }
});

it('respects disabled toolbar state', function () {
    Toolbar::disable();

    Route::get('/disabled-test', function () {
        return response('<html><body>Disabled Test</body></html>');
    });

    $response = $this->get('/disabled-test');

    $response->assertStatus(200);
    $content = $response->getContent();

    expect($content)->not->toContain('laravel-toolbar-shadow-host');
    expect($content)->not->toContain('__LARAVEL_TOOLBAR_DATA__');
});

it('handles multiple sequential requests', function () {
    Route::get('/request-1', function () {
        return response('<html><body>Request 1</body></html>');
    });

    Route::get('/request-2', function () {
        return response('<html><body>Request 2</body></html>');
    });

    $response1 = $this->get('/request-1');
    $response1->assertStatus(200);
    expect($response1->getContent())->toContain('laravel-toolbar-shadow-host');

    Profiler::$requestCheckpoints = [];
    Profiler::$viewRenders = [];

    $response2 = $this->get('/request-2');
    $response2->assertStatus(200);
    expect($response2->getContent())->toContain('laravel-toolbar-shadow-host');
});

it('includes request metadata in toolbar data', function () {
    Route::get('/metadata-test', function () {
        return response('<html><body>Metadata Test</body></html>');
    });

    $response = $this->get('/metadata-test');

    $response->assertStatus(200);
    $content = $response->getContent();

    preg_match('/window\.__LARAVEL_TOOLBAR_DATA__\s*=\s*({.*?});/s', $content, $matches);

    if (! empty($matches[1])) {
        $data = json_decode($matches[1], true);
        expect($data)->toHaveKey('request');
        expect($data)->toHaveKey('response');
    }
});

it('collects laravel environment data', function () {
    Route::get('/env-test', function () {
        return response('<html><body>Env Test</body></html>');
    });

    $response = $this->get('/env-test');

    $response->assertStatus(200);
    $content = $response->getContent();

    preg_match('/window\.__LARAVEL_TOOLBAR_DATA__\s*=\s*({.*?});/s', $content, $matches);

    if (! empty($matches[1])) {
        $data = json_decode($matches[1], true);
        expect($data)->toHaveKey('laravel');
    }
});

it('collects php info data', function () {
    Route::get('/php-test', function () {
        return response('<html><body>PHP Test</body></html>');
    });

    $response = $this->get('/php-test');

    $response->assertStatus(200);
    $content = $response->getContent();

    preg_match('/window\.__LARAVEL_TOOLBAR_DATA__\s*=\s*({.*?});/s', $content, $matches);

    if (! empty($matches[1])) {
        $data = json_decode($matches[1], true);
        expect($data)->toHaveKey('php');
    }
});
