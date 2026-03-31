<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Request;
use NckRtl\Toolbar\Mcp\Tools\GetRequestDataTool;

beforeEach(function () {
    Route::get('/mcp-request-target', fn () => response('ok'))->name('mcp.request.target');

    config()->set('app.url', 'http://localhost');
    config()->set('toolbar.request_data_allowed_environments', ['testing']);
});

it('MCP tool sends explicit auth headers from the new contract and returns summary data', function () {
    Http::fake(function (ClientRequest $request) {
        expect($request->hasHeader('X-REQUEST-ID'))->toBeTrue();
        expect($request->header('X-REQUEST-ID'))->toHaveCount(1);
        expect($request->header('X-TOOLBAR-AUTH'))->toBe(['user']);
        expect($request->header('X-TOOLBAR-USER'))->toBe(['42']);
        expect($request->hasHeader('X-MCP-ID'))->toBeFalse();

        $requestId = $request->header('X-REQUEST-ID')[0];

        Cache::put('laravel-toolbar-request-data-'.$requestId, mcpRequestPayload($requestId), 30);

        return Http::response('ok', 200);
    });

    $tool = new GetRequestDataTool;
    $response = $tool->handle(new Request([
        'route' => '/mcp-request-target',
        'type' => 'uri',
        'method' => 'GET',
        'auth_mode' => 'user',
        'user' => '42',
    ]));

    expect($response->isError())->toBeFalse();

    $payload = json_decode((string) $response->content(), true, 512, JSON_THROW_ON_ERROR);

    expect($payload)->toMatchArray([
        'summary' => [
            'auth_mode' => 'user',
            'request' => [
                'route_name' => 'mcp.request.target',
                'controller_action' => 'App\\Http\\Controllers\\McpRequestController@show',
            ],
            'profiler' => [
                'total_wall_time' => '95ms',
                'total_real_memory' => '1.5MB',
                'total_allocated_memory' => '3MB',
                'stages' => [
                    [
                        'label' => 'Application',
                        'duration' => '95ms',
                    ],
                ],
            ],
            'queries' => [
                'count' => 2,
                'slow_count' => 1,
                'duplicate_count' => 1,
            ],
        ],
    ]);

    expect($payload['raw']['metadata']['auth_mode'])->toBe('user');
    expect($payload['raw']['metadata']['auth_user_id'])->toBe('42');
    expect($payload['raw']['metadata']['request_id'])->not->toBeEmpty();
});

it('MCP tool requires a user id when auth_mode is user', function () {
    $tool = new GetRequestDataTool;

    $response = $tool->handle(new Request([
        'route' => '/mcp-request-target',
        'type' => 'uri',
        'method' => 'GET',
        'auth_mode' => 'user',
    ]));

    expect($response->isError())->toBeTrue();
    expect((string) $response->content())->toContain('user');
});

function mcpRequestPayload(string $requestId): array
{
    return [
        'metadata' => [
            'request_id' => $requestId,
            'auth_mode' => 'user',
            'auth_user_id' => '42',
        ],
        'request' => [
            'route_name' => 'mcp.request.target',
            'controller_action' => 'App\\Http\\Controllers\\McpRequestController@show',
        ],
        'profiler' => [
            'total_wall_time' => [
                'formattedValue' => '95ms',
            ],
            'total_real_memory' => [
                'formattedValue' => '1.5MB',
            ],
            'total_allocated_memory' => [
                'formattedValue' => '3MB',
            ],
            'stages' => [
                [
                    'label' => 'Application',
                    'wall_time' => [
                        'measurement' => [
                            'formattedValue' => '95ms',
                        ],
                    ],
                ],
            ],
        ],
        'queries' => [
            'queries' => [
                [
                    'sql' => 'select * from users',
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
