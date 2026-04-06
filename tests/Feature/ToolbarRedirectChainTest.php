<?php

declare(strict_types=1);

use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\Facades\Route;
use NckRtl\Toolbar\Support\RedirectChainStore;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    Toolbar::enable();

    $toolbar = app()->bound(Toolbar::class) ? app(Toolbar::class) : new Toolbar;
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);
});

function decodeToolbarPayload(string $html): array
{
    preg_match('/window\.__LARAVEL_TOOLBAR_DATA__\s*=\s*({.*?});/s', $html, $matches);

    return json_decode($matches[1] ?? '{}', true);
}

function decodeToolbarHeaderPayload(?string $header): array
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

function decodeToolbarSummaryPayload(?string $header): array
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

it('exempts the redirect chain cookie from cookie encryption', function () {
    $middleware = new EncryptCookies(Mockery::mock(EncrypterContract::class));

    expect($middleware->isDisabled(RedirectChainStore::COOKIE_NAME))->toBeTrue();
});

it('stores a redirect chain cookie on redirect responses', function () {
    Route::get('/redirect-start', fn () => redirect('/redirect-end'));
    Route::get('/redirect-end', fn () => response('<html><body>Done</body></html>'));

    $response = $this->get('/redirect-start');

    $response->assertRedirect('/redirect-end');
    $response->assertCookie(RedirectChainStore::COOKIE_NAME);
});

it('does not expose redirect_chain on html intermediary redirect responses', function () {
    Route::get('/html-redirect-start', fn () => response('<html><body>Redirecting...</body></html>', 302)->header('Location', '/html-redirect-end'));
    Route::get('/html-redirect-end', fn () => response('<html><body>Done</body></html>'));

    $response = $this->get('/html-redirect-start');
    $data = decodeToolbarPayload($response->getContent());

    $response->assertRedirect('/html-redirect-end');
    $response->assertCookie(RedirectChainStore::COOKIE_NAME);
    expect($data['request_id'])->not->toBeEmpty();
    expect($data)->not->toHaveKey('redirect_chain');
    expect($data)->not->toHaveKey('request_history');
    expect($data['response']['status_code'])->toBe(302);
});

it('tracks redirect chains for inertia header payloads and only exposes the current history row', function () {
    Route::post('/inertia-login', fn () => redirect('/inertia-redirect-one'));
    Route::get('/inertia-redirect-one', fn () => redirect('/inertia-dashboard'));
    Route::get('/inertia-dashboard', fn () => response()->json(['ok' => true]));

    $firstHop = $this->post('/inertia-login', [], ['X-Inertia' => 'true']);
    $firstHop->assertRedirect('/inertia-redirect-one');
    $firstHop->assertCookie(RedirectChainStore::COOKIE_NAME);
    $firstHop->assertHeader('x-toolbar');

    $firstPayload = decodeToolbarHeaderPayload($firstHop->headers->get('x-toolbar'));
    expect($firstPayload['request_id'])->not->toBeEmpty();
    expect($firstPayload['history_row']['id'])->toBe($firstPayload['request_id']);
    expect($firstPayload['history_row']['status_code'])->toBe(302);
    expect($firstPayload)->not->toHaveKey('redirect_chain');
    expect($firstPayload)->not->toHaveKey('request_history');

    $firstCookie = $firstHop->getCookie(RedirectChainStore::COOKIE_NAME, false);
    expect($firstCookie)->not->toBeNull();

    $secondHop = $this
        ->withUnencryptedCookie(RedirectChainStore::COOKIE_NAME, $firstCookie->getValue())
        ->get('/inertia-redirect-one', ['X-Inertia' => 'true']);
    $secondHop->assertRedirect('/inertia-dashboard');
    $secondHop->assertCookie(RedirectChainStore::COOKIE_NAME);
    $secondHop->assertHeader('x-toolbar');

    $secondPayload = decodeToolbarHeaderPayload($secondHop->headers->get('x-toolbar'));
    expect($secondPayload['request_id'])->not->toBeEmpty();
    expect($secondPayload['history_row']['id'])->toBe($secondPayload['request_id']);
    expect($secondPayload['history_row']['status_code'])->toBe(302);
    expect($secondPayload)->not->toHaveKey('redirect_chain');
    expect($secondPayload)->not->toHaveKey('request_history');

    $secondCookie = $secondHop->getCookie(RedirectChainStore::COOKIE_NAME, false);
    expect($secondCookie)->not->toBeNull();

    $terminal = $this
        ->withUnencryptedCookie(RedirectChainStore::COOKIE_NAME, $secondCookie->getValue())
        ->get('/inertia-dashboard', ['X-Inertia' => 'true']);
    $terminal->assertHeader('x-toolbar');
    $terminal->assertCookieExpired(RedirectChainStore::COOKIE_NAME);

    $terminalPayload = decodeToolbarHeaderPayload($terminal->headers->get('x-toolbar'));
    expect($terminalPayload['request_id'])->not->toBeEmpty();
    expect($terminalPayload['history_row'])->toMatchArray([
        'id' => $terminalPayload['request_id'],
        'is_xhr' => true,
        'method' => 'GET',
        'uri' => '/inertia-dashboard',
        'status_code' => 200,
    ]);
    expect($terminalPayload)->not->toHaveKey('redirect_chain');
    expect($terminalPayload)->not->toHaveKey('request_history');
    expect($terminalPayload)->not->toHaveKey('selected_request_id');
    expect($terminalPayload)->not->toHaveKey('response');
});

it('adds every redirect hop to the final html request_history payload', function () {
    Route::post('/login', fn () => redirect('/redirect-one'));
    Route::get('/redirect-one', fn () => redirect('/dashboard'));
    Route::get('/dashboard', fn () => response('<html><body>Dashboard</body></html>'));

    $firstHop = $this->post('/login');
    $firstHop->assertRedirect('/redirect-one');

    $firstCookie = $firstHop->getCookie(RedirectChainStore::COOKIE_NAME, false);
    expect($firstCookie)->not->toBeNull();

    $secondHop = $this
        ->withUnencryptedCookie(RedirectChainStore::COOKIE_NAME, $firstCookie->getValue())
        ->get('/redirect-one');
    $secondHop->assertRedirect('/dashboard');

    $secondCookie = $secondHop->getCookie(RedirectChainStore::COOKIE_NAME, false);
    expect($secondCookie)->not->toBeNull();

    $response = $this
        ->withUnencryptedCookie(RedirectChainStore::COOKIE_NAME, $secondCookie->getValue())
        ->get('/dashboard');

    $data = decodeToolbarPayload($response->getContent());

    expect($data['request_id'])->not->toBeEmpty();
    expect($data['selected_request_id'])->toBe($data['request_id']);
    expect($data['request_history'])->toHaveCount(3);
    expect($data['request_history'][0])->toMatchArray([
        'method' => 'POST',
        'uri' => '/login',
        'status_code' => 302,
    ]);
    expect($data['request_history'][2])->toMatchArray([
        'id' => $data['request_id'],
        'uri' => '/dashboard',
        'status_code' => 200,
    ]);
    expect($data['request_history'][0]['id'])->not->toBeEmpty();
    expect($data['request_history'][1]['id'])->not->toBeEmpty();
    expect($data['response']['status_code'])->toBe(200);
    expect($data)->not->toHaveKey('redirect_chain');
});

it('expires the redirect chain cookie after the terminal response', function () {
    Route::get('/redirect-start', fn () => redirect('/redirect-end'));
    Route::get('/redirect-end', fn () => response('<html><body>Done</body></html>'));

    $firstHop = $this->get('/redirect-start');
    $firstHop->assertRedirect('/redirect-end');

    $firstCookie = $firstHop->getCookie(RedirectChainStore::COOKIE_NAME, false);
    expect($firstCookie)->not->toBeNull();

    $response = $this
        ->withUnencryptedCookie(RedirectChainStore::COOKIE_NAME, $firstCookie->getValue())
        ->get('/redirect-end');

    $response->assertCookieExpired(RedirectChainStore::COOKIE_NAME);
});

it('clears redirect chain state when the terminal response cannot expose toolbar data', function () {
    Route::get('/redirect-json-start', fn () => redirect('/redirect-json-end'));
    Route::get('/redirect-json-end', fn () => response()->json(['ok' => true]));
    Route::get('/redirect-json-later', fn () => response('<html><body>Later</body></html>'));

    $firstHop = $this->get('/redirect-json-start');
    $firstHop->assertRedirect('/redirect-json-end');
    $firstHop->assertCookie(RedirectChainStore::COOKIE_NAME);

    $firstCookie = $firstHop->getCookie(RedirectChainStore::COOKIE_NAME, false);
    expect($firstCookie)->not->toBeNull();

    $terminal = $this
        ->withUnencryptedCookie(RedirectChainStore::COOKIE_NAME, $firstCookie->getValue())
        ->get('/redirect-json-end');
    $terminal->assertCookieExpired(RedirectChainStore::COOKIE_NAME);

    $later = $this
        ->withUnencryptedCookie(RedirectChainStore::COOKIE_NAME, $firstCookie->getValue())
        ->get('/redirect-json-later');
    $later->assertCookieExpired(RedirectChainStore::COOKIE_NAME);

    $laterPayload = decodeToolbarPayload($later->getContent());
    expect($laterPayload['request_history'])->toHaveCount(1);
    expect($laterPayload['request_history'][0]['uri'])->toBe('/redirect-json-later');
    expect($laterPayload)->not->toHaveKey('redirect_chain');
});

it('clears profiled redirect chain state on terminal profiled responses', function () {
    Route::get('/profiled-redirect-start', fn () => redirect('/profiled-redirect-end'));
    Route::get('/profiled-redirect-end', fn () => response()->json(['ok' => true]));
    Route::get('/profiled-redirect-later', fn () => response('<html><body>Later</body></html>'));

    $headers = ['X-REQUEST-ID' => 'profiled-redirect-chain'];

    $firstHop = $this->get('/profiled-redirect-start', $headers);
    $firstHop->assertRedirect('/profiled-redirect-end');
    $firstHop->assertCookie(RedirectChainStore::COOKIE_NAME);

    $firstSummary = decodeToolbarSummaryPayload($firstHop->headers->get('X-Toolbar-Summary'));
    expect($firstSummary['request_id'])->not->toBeEmpty();
    expect($firstSummary['request_id'])->not->toBe($headers['X-REQUEST-ID']);
    expect($firstSummary['profile_request_id'])->toBe($headers['X-REQUEST-ID']);

    $firstCookie = $firstHop->getCookie(RedirectChainStore::COOKIE_NAME, false);
    expect($firstCookie)->not->toBeNull();

    $terminal = $this
        ->withUnencryptedCookie(RedirectChainStore::COOKIE_NAME, $firstCookie->getValue())
        ->get('/profiled-redirect-end', $headers);
    $terminal->assertCookieExpired(RedirectChainStore::COOKIE_NAME);

    $terminalSummary = decodeToolbarSummaryPayload($terminal->headers->get('X-Toolbar-Summary'));
    expect($terminalSummary['request_id'])->not->toBeEmpty();
    expect($terminalSummary['request_id'])->not->toBe($headers['X-REQUEST-ID']);
    expect($terminalSummary['request_id'])->not->toBe($firstSummary['request_id']);
    expect($terminalSummary['profile_request_id'])->toBe($headers['X-REQUEST-ID']);

    $later = $this
        ->withUnencryptedCookie(RedirectChainStore::COOKIE_NAME, $firstCookie->getValue())
        ->get('/profiled-redirect-later');
    $later->assertCookieExpired(RedirectChainStore::COOKIE_NAME);

    $laterPayload = decodeToolbarPayload($later->getContent());
    expect($laterPayload['request_history'])->toHaveCount(1);
    expect($laterPayload['request_history'][0]['uri'])->toBe('/profiled-redirect-later');
    expect($laterPayload)->not->toHaveKey('redirect_chain');
});
