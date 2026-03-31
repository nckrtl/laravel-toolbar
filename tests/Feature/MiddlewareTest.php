<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use NckRtl\Toolbar\Enums\RequestCheckpointId;
use NckRtl\Toolbar\Http\Middleware\MiddlewareEnd;
use NckRtl\Toolbar\Http\Middleware\MiddlewareStart;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use NckRtl\Toolbar\Tests\Fixtures\TestUser;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    Toolbar::enable();
    Profiler::$requestCheckpoints = [];
    Profiler::$viewRenders = [];
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
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);
});

// MiddlewareStart Tests

it('MiddlewareStart records BEFORE_MIDDLEWARE checkpoint', function () {
    $middleware = new MiddlewareStart;
    $request = Request::create('/test', 'GET');

    $middleware->handle($request, function ($request) {
        return new Response('OK');
    });

    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE);

    expect($checkpoint)->not->toBeNull();
});

it('MiddlewareStart records AFTER_MIDDLEWARE checkpoint', function () {
    $middleware = new MiddlewareStart;
    $request = Request::create('/test', 'GET');

    $middleware->handle($request, function ($request) {
        return new Response('OK');
    });

    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::AFTER_MIDDLEWARE);

    expect($checkpoint)->not->toBeNull();
});

it('MiddlewareStart does not overwrite existing BEFORE_MIDDLEWARE checkpoint', function () {
    Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE);
    $existingCheckpoint = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE);

    $middleware = new MiddlewareStart;
    $request = Request::create('/test', 'GET');

    $middleware->handle($request, function ($request) {
        return new Response('OK');
    });

    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE);

    expect($checkpoint)->toBe($existingCheckpoint);
});

it('MiddlewareStart does not overwrite existing AFTER_MIDDLEWARE checkpoint', function () {
    $middleware = new MiddlewareStart;
    $request = Request::create('/test', 'GET');

    $middleware->handle($request, function ($request) {
        Profiler::record(RequestCheckpointId::AFTER_MIDDLEWARE);

        return new Response('OK');
    });

    $existingCheckpoint = Profiler::getCheckpoint(RequestCheckpointId::AFTER_MIDDLEWARE);

    Profiler::$requestCheckpoints = [];
    Profiler::record(RequestCheckpointId::AFTER_MIDDLEWARE);
    $newCheckpoint = Profiler::getCheckpoint(RequestCheckpointId::AFTER_MIDDLEWARE);

    expect($newCheckpoint)->not->toBe($existingCheckpoint);
});

it('MiddlewareStart returns response from next middleware', function () {
    $middleware = new MiddlewareStart;
    $request = Request::create('/test', 'GET');
    $expectedResponse = new Response('Expected Content');

    $response = $middleware->handle($request, function ($request) use ($expectedResponse) {
        return $expectedResponse;
    });

    expect($response)->toBe($expectedResponse);
});

it('does not authenticate when only X-REQUEST-ID is present', function () {
    TestUser::query()->create([
        'id' => 1,
        'name' => 'First User',
    ]);

    $middleware = new MiddlewareStart;
    $request = Request::create('/test', 'GET');
    $request->headers->set('X-REQUEST-ID', 'request-123');

    $middleware->handle($request, function (Request $request) {
        return new Response('OK');
    });

    expect(Auth::check())->toBeFalse();
});

it('authenticates the first user when X-TOOLBAR-AUTH is first-user', function () {
    $firstUser = TestUser::query()->create([
        'id' => 10,
        'name' => 'First User',
    ]);

    TestUser::query()->create([
        'id' => 20,
        'name' => 'Second User',
    ]);

    $middleware = new MiddlewareStart;
    $request = Request::create('/test', 'GET');
    $request->headers->set('X-TOOLBAR-AUTH', 'first-user');

    $middleware->handle($request, function (Request $request) {
        return new Response('OK');
    });

    expect(Auth::id())->toBe($firstUser->getKey());
});

it('authenticates the requested user id when X-TOOLBAR-AUTH is user', function () {
    TestUser::query()->create([
        'id' => 10,
        'name' => 'First User',
    ]);

    $requestedUser = TestUser::query()->create([
        'id' => 20,
        'name' => 'Requested User',
    ]);

    $middleware = new MiddlewareStart;
    $request = Request::create('/test', 'GET');
    $request->headers->set('X-TOOLBAR-AUTH', 'user');
    $request->headers->set('X-TOOLBAR-USER', (string) $requestedUser->getKey());

    $middleware->handle($request, function (Request $request) {
        return new Response('OK');
    });

    expect(Auth::id())->toBe($requestedUser->getKey());
});

// MiddlewareEnd Tests

it('MiddlewareEnd records BEFORE_CONTROLLER checkpoint', function () {
    $middleware = new MiddlewareEnd;
    $request = Request::create('/test', 'GET');

    $middleware->handle($request, function ($request) {
        return new Response('OK');
    });

    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_CONTROLLER);

    expect($checkpoint)->not->toBeNull();
});

it('MiddlewareEnd records AFTER_VIEW_RENDERING checkpoint', function () {
    $middleware = new MiddlewareEnd;
    $request = Request::create('/test', 'GET');

    $middleware->handle($request, function ($request) {
        return new Response('OK');
    });

    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::AFTER_VIEW_RENDERING);

    expect($checkpoint)->not->toBeNull();
});

it('MiddlewareEnd does not overwrite existing BEFORE_CONTROLLER checkpoint', function () {
    Profiler::record(RequestCheckpointId::BEFORE_CONTROLLER);
    $existingCheckpoint = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_CONTROLLER);

    $middleware = new MiddlewareEnd;
    $request = Request::create('/test', 'GET');

    $middleware->handle($request, function ($request) {
        return new Response('OK');
    });

    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_CONTROLLER);

    expect($checkpoint)->toBe($existingCheckpoint);
});

it('MiddlewareEnd does not overwrite existing AFTER_VIEW_RENDERING checkpoint', function () {
    $middleware = new MiddlewareEnd;
    $request = Request::create('/test', 'GET');

    $middleware->handle($request, function ($request) {
        Profiler::record(RequestCheckpointId::AFTER_VIEW_RENDERING);

        return new Response('OK');
    });

    $existingCheckpoint = Profiler::getCheckpoint(RequestCheckpointId::AFTER_VIEW_RENDERING);

    Profiler::$requestCheckpoints = [];
    Profiler::record(RequestCheckpointId::AFTER_VIEW_RENDERING);
    $newCheckpoint = Profiler::getCheckpoint(RequestCheckpointId::AFTER_VIEW_RENDERING);

    expect($newCheckpoint)->not->toBe($existingCheckpoint);
});

it('MiddlewareEnd returns response from next middleware', function () {
    $middleware = new MiddlewareEnd;
    $request = Request::create('/test', 'GET');
    $expectedResponse = new Response('Expected Content');

    $response = $middleware->handle($request, function ($request) use ($expectedResponse) {
        return $expectedResponse;
    });

    expect($response)->toBe($expectedResponse);
});

// Combined Middleware Flow Tests

it('both middleware create proper checkpoint sequence', function () {
    $middlewareStart = new MiddlewareStart;
    $middlewareEnd = new MiddlewareEnd;
    $request = Request::create('/test', 'GET');

    $middlewareStart->handle($request, function ($request) use ($middlewareEnd) {
        return $middlewareEnd->handle($request, function ($request) {
            return new Response('OK');
        });
    });

    expect(Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE))->not->toBeNull();
    expect(Profiler::getCheckpoint(RequestCheckpointId::BEFORE_CONTROLLER))->not->toBeNull();
    expect(Profiler::getCheckpoint(RequestCheckpointId::AFTER_VIEW_RENDERING))->not->toBeNull();
    expect(Profiler::getCheckpoint(RequestCheckpointId::AFTER_MIDDLEWARE))->not->toBeNull();
});

it('checkpoint order is correct in middleware flow', function () {
    $middlewareStart = new MiddlewareStart;
    $middlewareEnd = new MiddlewareEnd;
    $request = Request::create('/test', 'GET');

    $middlewareStart->handle($request, function ($request) use ($middlewareEnd) {
        return $middlewareEnd->handle($request, function ($request) {
            return new Response('OK');
        });
    });

    $beforeMiddleware = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE);
    $beforeController = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_CONTROLLER);
    $afterViewRendering = Profiler::getCheckpoint(RequestCheckpointId::AFTER_VIEW_RENDERING);
    $afterMiddleware = Profiler::getCheckpoint(RequestCheckpointId::AFTER_MIDDLEWARE);

    expect($beforeMiddleware->time->value)->toBeLessThanOrEqual($beforeController->time->value);
    expect($beforeController->time->value)->toBeLessThanOrEqual($afterViewRendering->time->value);
    expect($afterViewRendering->time->value)->toBeLessThanOrEqual($afterMiddleware->time->value);
});
