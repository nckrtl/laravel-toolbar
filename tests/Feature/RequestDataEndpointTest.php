<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/request-profile-target', fn () => response('ok'))->name('request.profile.target');

    config()->set('app.key', str_repeat('a', 32));
    config()->set('app.url', 'http://localhost');
    config()->set('toolbar.request_data_allowed_environments', ['testing']);
});

it('request endpoint returns cached request data by request id', function () {
    $requestId = 'request-123';

    Cache::put('laravel-toolbar-request-data-'.$requestId, requestProfilePayload(), 30);

    $response = $this->getJson('/_toolbar/requests/'.$requestId);

    $response->assertOk();
    $response->assertJson([
        'summary' => [
            'auth_mode' => 'user',
            'request' => [
                'route_name' => 'request.profile.target',
                'controller_action' => 'App\\Http\\Controllers\\RequestProfileController@index',
            ],
            'profiler' => [
                'total_wall_time' => '120ms',
                'total_real_memory' => '2MB',
                'total_allocated_memory' => '4MB',
                'stages' => [
                    [
                        'label' => 'Bootstrap',
                        'duration' => '10ms',
                    ],
                    [
                        'label' => 'Controller',
                        'duration' => '110ms',
                    ],
                ],
            ],
            'queries' => [
                'count' => 3,
                'slow_count' => 1,
                'duplicate_count' => 1,
            ],
        ],
        'raw' => [
            'metadata' => [
                'request_id' => $requestId,
                'auth_mode' => 'user',
                'auth_user_id' => '42',
            ],
        ],
    ]);
});

it('request endpoint returns 404 for unknown ids', function () {
    $response = $this->getJson('/_toolbar/requests/missing-request');

    $response->assertNotFound();
});

it('request endpoint returns 404 when request data routes are disabled for current environment', function () {
    config()->set('toolbar.request_data_allowed_environments', ['local']);

    $requestId = 'request-123';
    Cache::put('laravel-toolbar-request-data-'.$requestId, requestProfilePayload(), 30);

    $response = $this->getJson('/_toolbar/requests/'.$requestId);

    $response->assertNotFound();
});

function requestProfilePayload(): array
{
    return [
        'metadata' => [
            'request_id' => 'request-123',
            'auth_mode' => 'user',
            'auth_user_id' => '42',
        ],
        'request' => [
            'route_name' => 'request.profile.target',
            'controller_action' => 'App\\Http\\Controllers\\RequestProfileController@index',
        ],
        'profiler' => [
            'total_wall_time' => [
                'formattedValue' => '120ms',
            ],
            'total_real_memory' => [
                'formattedValue' => '2MB',
            ],
            'total_allocated_memory' => [
                'formattedValue' => '4MB',
            ],
            'stages' => [
                [
                    'label' => 'Bootstrap',
                    'wall_time' => [
                        'measurement' => [
                            'formattedValue' => '10ms',
                        ],
                    ],
                ],
                [
                    'label' => 'Controller',
                    'wall_time' => [
                        'measurement' => [
                            'formattedValue' => '110ms',
                        ],
                    ],
                ],
            ],
        ],
        'queries' => [
            'queries' => [
                [
                    'sql' => 'select * from users',
                    'is_slow' => false,
                    'is_duplicate' => false,
                ],
                [
                    'sql' => 'select * from posts',
                    'is_slow' => true,
                    'is_duplicate' => false,
                ],
                [
                    'sql' => 'select * from users',
                    'is_slow' => false,
                    'is_duplicate' => true,
                ],
            ],
        ],
    ];
}
