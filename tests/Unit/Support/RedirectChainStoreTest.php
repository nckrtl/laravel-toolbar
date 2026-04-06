<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use NckRtl\Toolbar\Support\RedirectChainStore;

beforeEach(function () {
    Cache::flush();
});

it('reads the current chain id from the request cookie', function () {
    $store = new RedirectChainStore;
    $request = Request::create(
        '/redirected',
        'GET',
        [],
        [RedirectChainStore::COOKIE_NAME => (string) Str::uuid()],
    );

    expect($store->currentChainId($request))->toMatch('/^[0-9a-fA-F-]{36}$/');
});

it('returns null when the current chain id cookie is not a valid uuid', function () {
    $store = new RedirectChainStore;
    $request = Request::create(
        '/redirected',
        'GET',
        [],
        [RedirectChainStore::COOKIE_NAME => 'chain-123'],
    );

    expect($store->currentChainId($request))->toBeNull();
});

it('appends request history rows to the cached redirect chain', function () {
    $store = new RedirectChainStore;

    $store->append('chain-123', [
        'id' => 'request-123',
        'is_xhr' => false,
        'method' => 'POST',
        'uri' => '/login',
        'name' => 'login',
        'middleware_count' => 2,
        'status_code' => 302,
        'size' => '0B',
        'duration' => '12ms',
    ]);

    expect(Cache::get('laravel-toolbar-redirect-chain-chain-123'))->toBe([
        [
            'id' => 'request-123',
            'is_xhr' => false,
            'method' => 'POST',
            'uri' => '/login',
            'name' => 'login',
            'middleware_count' => 2,
            'status_code' => 302,
            'size' => '0B',
            'duration' => '12ms',
        ],
    ]);
});

it('consumes the chain and forgets the cache entry', function () {
    Cache::put('laravel-toolbar-redirect-chain-chain-123', [
        [
            'id' => 'request-123',
            'is_xhr' => false,
            'method' => 'POST',
            'uri' => '/login',
            'name' => 'login',
            'middleware_count' => 2,
            'status_code' => 302,
            'size' => '0B',
            'duration' => '12ms',
        ],
    ], 30);

    $store = new RedirectChainStore;

    $chain = $store->consume('chain-123', [
        'id' => 'request-456',
        'is_xhr' => false,
        'method' => 'GET',
        'uri' => '/dashboard',
        'name' => 'dashboard',
        'middleware_count' => 1,
        'status_code' => 200,
        'size' => '9B',
        'duration' => '3ms',
    ]);

    expect($chain)->toHaveCount(2);
    expect($chain[1]['id'])->toBe('request-456');
    expect($chain[1]['uri'])->toBe('/dashboard');
    expect(Cache::get('laravel-toolbar-redirect-chain-chain-123'))->toBeNull();
});

it('creates a chain cookie with the configured ttl', function () {
    config(['toolbar.request_data_ttl' => 45]);

    $store = new RedirectChainStore;
    $cookie = $store->makeChainCookie((string) Str::uuid());

    expect($cookie->getName())->toBe(RedirectChainStore::COOKIE_NAME);
    expect($cookie->getPath())->toBe('/');
    expect($cookie->getExpiresTime())->toBeGreaterThan(time());
});

it('creates a forget cookie that immediately expires', function () {
    $store = new RedirectChainStore;
    $cookie = $store->makeForgetCookie();

    expect($cookie->getName())->toBe(RedirectChainStore::COOKIE_NAME);
    expect($cookie->getValue())->toBe('');
    expect($cookie->getPath())->toBe('/');
    expect($cookie->getExpiresTime())->toBeLessThan(time());
});
