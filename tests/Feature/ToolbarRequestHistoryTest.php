<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    Toolbar::enable();

    $toolbar = app()->bound(Toolbar::class) ? app(Toolbar::class) : new Toolbar;
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);
});

function decodeRequestHistoryToolbarPayload(string $html): array
{
    preg_match('/window\.__LARAVEL_TOOLBAR_DATA__\s*=\s*({.*?});/s', $html, $matches);

    return json_decode($matches[1] ?? '{}', true);
}

function decodeRequestHistoryHeaderPayload(?string $header): array
{
    if (! is_string($header) || $header === '') {
        return [];
    }

    $decoded = base64_decode($header, true);

    if ($decoded === false) {
        return [];
    }

    return json_decode($decoded, true) ?? [];
}

function decodeRequestHistorySummaryPayload(?string $header): array
{
    if (! is_string($header) || $header === '') {
        return [];
    }

    $decoded = base64_decode($header, true);

    if ($decoded === false) {
        return [];
    }

    return json_decode($decoded, true) ?? [];
}

it('adds request_history metadata to final html responses', function () {
    Route::get('/request-history-page', fn () => response('<html><body>History</body></html>'))
        ->name('request.history.page');

    $response = $this->get('/request-history-page');
    $data = decodeRequestHistoryToolbarPayload($response->getContent());

    expect($data['request_id'])->not->toBeEmpty();
    expect($data['selected_request_id'])->toBe($data['request_id']);
    expect($data['request_history'])->toHaveCount(1);
    expect($data['request_history'][0])->toMatchArray([
        'id' => $data['request_id'],
        'is_xhr' => false,
        'method' => 'GET',
        'uri' => '/request-history-page',
        'name' => 'request.history.page',
        'status_code' => 200,
        'response_type' => 'HTML',
    ]);
    expect($data['response']['status_code'])->toBe(200);
    expect($data)->not->toHaveKey('redirect_chain');
});

it('adds compact request history metadata to xhr responses', function () {
    Route::get('/request-history-xhr', fn () => response()->json(['ok' => true]))
        ->name('request.history.xhr');

    $response = $this->get('/request-history-xhr', [
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ]);

    $response->assertHeader('x-toolbar');

    $data = decodeRequestHistoryHeaderPayload($response->headers->get('x-toolbar'));

    expect($data['request_id'])->not->toBeEmpty();
    expect($data['history_row'])->toMatchArray([
        'id' => $data['request_id'],
        'is_xhr' => true,
        'method' => 'GET',
        'uri' => '/request-history-xhr',
        'name' => 'request.history.xhr',
        'status_code' => 200,
        'response_type' => 'JSON',
    ]);
    expect($data)->not->toHaveKey('request_history');
    expect($data)->not->toHaveKey('selected_request_id');
    expect($data)->not->toHaveKey('response');
    expect($data)->not->toHaveKey('redirect_chain');
});

it('resets request history for inertia get responses in header payloads', function () {
    Route::get('/request-history-inertia-page', function () {
        return response()->json([
            'component' => 'Users/Index',
            'props' => [],
            'url' => '/request-history-inertia-page',
            'version' => '1',
        ])->header('X-Inertia', 'true');
    })->name('request.history.inertia.page');

    $response = $this->get('/request-history-inertia-page', [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'text/html, application/xhtml+xml',
    ]);

    $data = decodeRequestHistoryHeaderPayload($response->headers->get('x-toolbar'));

    expect($data['request_id'])->not->toBeEmpty();
    expect($data['selected_request_id'])->toBe($data['request_id']);
    expect($data['request_history'])->toHaveCount(1);
    expect($data['request_history'][0])->toMatchArray([
        'id' => $data['request_id'],
        'is_xhr' => false,
        'method' => 'GET',
        'uri' => '/request-history-inertia-page',
        'name' => 'request.history.inertia.page',
        'status_code' => 200,
        'response_type' => 'Inertia',
    ]);
    expect($data['response']['status_code'])->toBe(200);
    expect($data)->not->toHaveKey('history_row');
});

it('keeps inertia validation style responses as compact async history rows', function () {
    Route::post('/request-history-inertia-validation', function () {
        return response()->json([
            'errors' => [
                'email' => 'The email field is required.',
            ],
        ], 422)->header('X-Inertia', 'true');
    })->name('request.history.inertia.validation');

    $response = $this->post('/request-history-inertia-validation', [], [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ]);

    $data = decodeRequestHistoryHeaderPayload($response->headers->get('x-toolbar'));

    expect($data['request_id'])->not->toBeEmpty();
    expect($data['history_row'])->toMatchArray([
        'id' => $data['request_id'],
        'is_xhr' => true,
        'method' => 'POST',
        'uri' => '/request-history-inertia-validation',
        'name' => 'request.history.inertia.validation',
        'status_code' => 422,
        'response_type' => 'Client Error',
    ]);
    expect($data)->not->toHaveKey('request_history');
    expect($data)->not->toHaveKey('selected_request_id');
});

it('adds a generated request_id to profiled summaries for endpoint lookup', function () {
    Route::get('/request-history-profiled', fn () => response()->json(['ok' => true]))
        ->name('request.history.profiled');

    $response = $this->get('/request-history-profiled', [
        'X-REQUEST-ID' => 'profile-request-123',
    ]);

    $summary = decodeRequestHistorySummaryPayload($response->headers->get('X-Toolbar-Summary'));

    expect($summary['request_id'])->not->toBeEmpty();
    expect($summary['request_id'])->not->toBe('profile-request-123');
    expect($summary['profile_request_id'])->toBe('profile-request-123');
});
