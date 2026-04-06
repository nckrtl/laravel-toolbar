<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Collectors\LaravelCollector;
use NckRtl\Toolbar\Collectors\PhpCollector;
use NckRtl\Toolbar\Http\Middleware\MiddlewareStart;
use NckRtl\Toolbar\Tests\Fixtures\TestUser;
use NckRtl\Toolbar\Tests\Helpers\MockResponse;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    Toolbar::enable();
    Auth::logout();

    Schema::dropIfExists('users');
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
    });

    config()->set('login-link.allowed_environments', ['testing']);
    config()->set('login-link.user_model', TestUser::class);
    config()->set('auth.guards.web', [
        'driver' => 'session',
        'provider' => 'users',
    ]);
    config()->set('auth.providers.users', [
        'driver' => 'eloquent',
        'model' => TestUser::class,
    ]);

    $toolbar = new Toolbar;
    $toolbar->config->collectors([]);
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);
    app()->instance('request', Request::create('/test', 'GET'));
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
    expect($data['metadata'])->toHaveKey('request_id');
    expect($data['metadata'])->toHaveKey('auth_mode');
    expect($data['metadata'])->toHaveKey('auth_user_id');
    expect($data['metadata']['collectors'])->toContain('No collectors enabled');
    expect($data['metadata']['request_id'])->toBeNull();
    expect($data['metadata']['auth_mode'])->toBe('guest');
    expect($data['metadata']['auth_user_id'])->toBeNull();
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
    expect($cachedData['metadata']['request_id'])->toBeNull();
    expect($cachedData['metadata']['auth_mode'])->toBe('guest');
    expect($cachedData['metadata']['auth_user_id'])->toBeNull();
});

it('caches profiled request data under the generated snapshot id and aliases X-REQUEST-ID', function () {
    Cache::flush();

    $requestId = 'test-request-id-123';

    $request = Request::create('/test', 'GET');
    $request->headers->set('X-REQUEST-ID', $requestId);
    $request->attributes->set(\NckRtl\Toolbar\Support\ProfileRequestContext::SNAPSHOT_REQUEST_ID_ATTRIBUTE, 'snapshot-request-123');
    $request->headers->set('X-TOOLBAR-AUTH', 'user');
    $request->headers->set('X-TOOLBAR-USER', '42');
    app()->instance('request', $request);

    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        new PhpCollector,
    ]);

    $manager = new CollectorManager;
    $data = $manager->collectData();

    $cachedData = Cache::get('laravel-toolbar-request-data-snapshot-request-123');
    $aliasedData = Cache::get('laravel-toolbar-request-data-'.$requestId);

    expect($cachedData)->not->toBeNull();
    expect($aliasedData)->not->toBeNull();
    expect($cachedData)->toHaveKey('php');
    expect($cachedData['metadata']['request_id'])->toBe('snapshot-request-123');
    expect($cachedData['metadata']['profile_request_id'])->toBe($requestId);
    expect($cachedData['metadata']['auth_mode'])->toBe('guest');
    expect($cachedData['metadata']['auth_user_id'])->toBeNull();
    expect($data['metadata']['request_id'])->toBe('snapshot-request-123');
    expect($data['metadata']['profile_request_id'])->toBe($requestId);
});

it('stores first-user metadata with the actual authenticated user id', function () {
    Cache::flush();

    $firstUser = TestUser::query()->create([
        'id' => 10,
        'name' => 'First User',
    ]);

    TestUser::query()->create([
        'id' => 20,
        'name' => 'Second User',
    ]);

    $requestId = 'first-user-request';
    $request = Request::create('/test', 'GET');
    $request->headers->set('X-REQUEST-ID', $requestId);
    $request->attributes->set(\NckRtl\Toolbar\Support\ProfileRequestContext::SNAPSHOT_REQUEST_ID_ATTRIBUTE, 'snapshot-first-user-request');
    $request->headers->set('X-TOOLBAR-AUTH', 'first-user');

    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        new PhpCollector,
    ]);

    $middleware = new MiddlewareStart;

    $middleware->handle($request, function (Request $request) {
        app()->instance('request', $request);

        (new CollectorManager)->collectData();

        return new Response('OK');
    });

    $cachedData = Cache::get('laravel-toolbar-request-data-snapshot-first-user-request');

    expect($cachedData['metadata']['auth_mode'])->toBe('first-user');
    expect($cachedData['metadata']['auth_user_id'])->toBe($firstUser->getKey());
    expect($cachedData['metadata']['request_id'])->toBe('snapshot-first-user-request');
    expect($cachedData['metadata']['profile_request_id'])->toBe($requestId);
});

it('stores guest metadata when explicit user id is invalid', function () {
    Cache::flush();

    TestUser::query()->create([
        'id' => 10,
        'name' => 'First User',
    ]);

    $requestId = 'invalid-user-request';
    $request = Request::create('/test', 'GET');
    $request->headers->set('X-REQUEST-ID', $requestId);
    $request->attributes->set(\NckRtl\Toolbar\Support\ProfileRequestContext::SNAPSHOT_REQUEST_ID_ATTRIBUTE, 'snapshot-invalid-user-request');
    $request->headers->set('X-TOOLBAR-AUTH', 'user');
    $request->headers->set('X-TOOLBAR-USER', '999');

    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        new PhpCollector,
    ]);

    $middleware = new MiddlewareStart;

    $middleware->handle($request, function (Request $request) {
        app()->instance('request', $request);

        (new CollectorManager)->collectData();

        return new Response('OK');
    });

    $cachedData = Cache::get('laravel-toolbar-request-data-snapshot-invalid-user-request');

    expect(Auth::check())->toBeFalse();
    expect($cachedData['metadata']['auth_mode'])->toBe('guest');
    expect($cachedData['metadata']['auth_user_id'])->toBeNull();
    expect($cachedData['metadata']['request_id'])->toBe('snapshot-invalid-user-request');
    expect($cachedData['metadata']['profile_request_id'])->toBe($requestId);
});

it('stores guest metadata when auth mode user is missing a user id', function () {
    Cache::flush();

    TestUser::query()->create([
        'id' => 10,
        'name' => 'First User',
    ]);

    $requestId = 'missing-user-request';
    $request = Request::create('/test', 'GET');
    $request->headers->set('X-REQUEST-ID', $requestId);
    $request->attributes->set(\NckRtl\Toolbar\Support\ProfileRequestContext::SNAPSHOT_REQUEST_ID_ATTRIBUTE, 'snapshot-missing-user-request');
    $request->headers->set('X-TOOLBAR-AUTH', 'user');
    app()->instance('request', $request);

    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        new PhpCollector,
    ]);

    $manager = new CollectorManager;
    $manager->collectData();

    $cachedData = Cache::get('laravel-toolbar-request-data-snapshot-missing-user-request');

    expect($cachedData['metadata']['auth_mode'])->toBe('guest');
    expect($cachedData['metadata']['auth_user_id'])->toBeNull();
    expect($cachedData['metadata']['request_id'])->toBe('snapshot-missing-user-request');
    expect($cachedData['metadata']['profile_request_id'])->toBe($requestId);
});

it('caches metadata-only profiled payloads under the generated snapshot id and aliases X-REQUEST-ID', function () {
    Cache::flush();

    $requestId = 'metadata-only-request';
    $request = Request::create('/test', 'GET');
    $request->headers->set('X-REQUEST-ID', $requestId);
    $request->attributes->set(\NckRtl\Toolbar\Support\ProfileRequestContext::SNAPSHOT_REQUEST_ID_ATTRIBUTE, 'snapshot-metadata-only-request');
    app()->instance('request', $request);

    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([]);

    $data = (new CollectorManager)->collectData();
    $cachedData = Cache::get('laravel-toolbar-request-data-snapshot-metadata-only-request');
    $aliasedData = Cache::get('laravel-toolbar-request-data-'.$requestId);

    expect($data['metadata']['collectors'])->toContain('No collectors enabled');
    expect($cachedData)->not->toBeNull();
    expect($aliasedData)->not->toBeNull();
    expect($cachedData['metadata']['request_id'])->toBe('snapshot-metadata-only-request');
    expect($cachedData['metadata']['profile_request_id'])->toBe($requestId);
    expect($cachedData['metadata']['auth_mode'])->toBe('guest');
});

it('uses the configured request data ttl when caching', function () {
    config()->set('toolbar.request_data_ttl', 45);

    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        new PhpCollector,
    ]);

    Cache::shouldReceive('put')
        ->once()
        ->withArgs(function (string $key, array $data, int $ttl): bool {
            return str_starts_with($key, 'laravel-toolbar-request-data-')
                && array_key_exists('php', $data)
                && $ttl === 45;
        });

    $manager = new CollectorManager;
    $manager->collectData();
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
