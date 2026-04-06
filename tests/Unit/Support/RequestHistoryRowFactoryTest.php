<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use NckRtl\Toolbar\Support\RequestHistoryRowFactory;

it('builds a request history row from collected payload data', function () {
    $factory = new RequestHistoryRowFactory;
    $request = Request::create('/login', 'POST');

    $row = $factory->fromPayload($request, [
        'metadata' => [
            'id' => 'generated-request-id',
            'request_id' => null,
            'wall_time' => [
                'total' => '18.40ms',
            ],
        ],
        'request' => [
            'method' => 'POST',
            'uri' => '/login',
            'route_name' => 'login.store',
            'controller_action' => 'App\\Http\\Controllers\\LoginController@store',
            'middleware' => ['web', 'guest'],
            'is_inertia' => true,
        ],
        'response' => [
            'status_code' => 302,
            'size' => [
                'formattedValue' => '0B',
                'value' => 0,
                'unit' => 'B',
            ],
        ],
    ]);

    expect($row)->toBe([
        'id' => 'generated-request-id',
        'is_xhr' => false,
        'method' => 'POST',
        'uri' => '/login',
        'name' => 'login.store',
        'action' => 'App\\Http\\Controllers\\LoginController@store',
        'middleware_count' => 2,
        'status_code' => 302,
        'size' => '0B',
        'duration' => '18.40ms',
        'response_type' => 'Inertia Redirect',
    ]);
});

it('builds a request history row from the request and response fallback path', function () {
    Route::get('/request-history-row-target', fn () => response('ok'))
        ->middleware(['web', 'auth'])
        ->name('request.history.row.target');

    $request = Request::create('/request-history-row-target', 'GET', [], [], [], [
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_ACCEPT' => 'application/json',
    ]);

    $route = app('router')->getRoutes()->match($request);
    $request->setRouteResolver(fn () => $route);

    $factory = new RequestHistoryRowFactory;

    $row = $factory->fromRequest($request, response('ok', 201), 'request-456');

    expect($row)->toBe([
        'id' => 'request-456',
        'is_xhr' => true,
        'method' => 'GET',
        'uri' => '/request-history-row-target',
        'name' => 'request.history.row.target',
        'action' => null,
        'middleware_count' => count(array_values($route->gatherMiddleware())),
        'status_code' => 201,
        'size' => '2 B',
        'duration' => null,
        'response_type' => 'JSON',
    ]);
});

it('classifies html, redirect, client error, server error, and other response types', function () {
    $factory = new RequestHistoryRowFactory;

    $request = Request::create('/page', 'GET', [], [], [], [
        'HTTP_ACCEPT' => 'text/html',
    ]);

    $htmlRow = $factory->fromPayload($request, [
        'metadata' => [
            'id' => 'request-html',
        ],
        'request' => [
            'method' => 'GET',
            'uri' => '/page',
            'route_name' => 'page.show',
            'middleware' => [],
            'is_inertia' => false,
        ],
        'response' => [
            'status_code' => 200,
            'headers' => [
                'content-type' => ['text/html; charset=UTF-8'],
            ],
            'size' => [
                'formattedValue' => '42B',
            ],
        ],
    ]);

    $redirectRow = $factory->fromPayload($request, [
        'metadata' => [
            'id' => 'request-redirect',
        ],
        'request' => [
            'method' => 'GET',
            'uri' => '/redirect',
            'route_name' => 'redirect.show',
            'middleware' => [],
            'is_inertia' => false,
        ],
        'response' => [
            'status_code' => 302,
            'headers' => [
                'content-type' => ['text/html; charset=UTF-8'],
            ],
            'size' => [
                'formattedValue' => '0B',
            ],
        ],
    ]);

    $clientErrorRow = $factory->fromPayload($request, [
        'metadata' => [
            'id' => 'request-client-error',
        ],
        'request' => [
            'method' => 'GET',
            'uri' => '/missing',
            'route_name' => 'missing.show',
            'middleware' => [],
            'is_inertia' => false,
        ],
        'response' => [
            'status_code' => 404,
            'headers' => [],
            'size' => [
                'formattedValue' => '0B',
            ],
        ],
    ]);

    $serverErrorRow = $factory->fromPayload($request, [
        'metadata' => [
            'id' => 'request-server-error',
        ],
        'request' => [
            'method' => 'GET',
            'uri' => '/broken',
            'route_name' => 'broken.show',
            'middleware' => [],
            'is_inertia' => false,
        ],
        'response' => [
            'status_code' => 500,
            'headers' => [],
            'size' => [
                'formattedValue' => '0B',
            ],
        ],
    ]);

    $otherRow = $factory->fromPayload($request, [
        'metadata' => [
            'id' => 'request-other',
        ],
        'request' => [
            'method' => 'GET',
            'uri' => '/download',
            'route_name' => 'download.show',
            'middleware' => [],
            'is_inertia' => false,
        ],
        'response' => [
            'status_code' => 200,
            'headers' => [
                'content-type' => ['application/octet-stream'],
            ],
            'size' => [
                'formattedValue' => '5KB',
            ],
        ],
    ]);

    expect($htmlRow['response_type'])->toBe('HTML');
    expect($redirectRow['response_type'])->toBe('Redirect');
    expect($clientErrorRow['response_type'])->toBe('Client Error');
    expect($serverErrorRow['response_type'])->toBe('Server Error');
    expect($otherRow['response_type'])->toBe('Other');
});
