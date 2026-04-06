<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

final class RedirectChainStore
{
    public const COOKIE_NAME = 'laravel_toolbar_redirect_chain';

    private const CACHE_PREFIX = 'laravel-toolbar-redirect-chain-';

    public function currentChainId(Request $request): ?string
    {
        $value = $request->cookies->get(self::COOKIE_NAME);

        return is_string($value) && Str::isUuid($value) ? $value : null;
    }

    public function createChainId(): string
    {
        return (string) Str::uuid();
    }

    public function append(string $chainId, array $hop): void
    {
        $chain = $this->load($chainId);
        $chain[] = $this->normalizeHop($hop);

        Cache::put($this->cacheKey($chainId), $chain, config('toolbar.request_data_ttl', 30));
    }

    public function consume(string $chainId, array $finalHop): array
    {
        $chain = $this->load($chainId);
        $chain[] = $this->normalizeHop($finalHop);

        $this->forget($chainId);

        return $chain;
    }

    public function load(string $chainId): array
    {
        $chain = Cache::get($this->cacheKey($chainId), []);

        return is_array($chain) ? array_values($chain) : [];
    }

    public function forget(string $chainId): void
    {
        Cache::forget($this->cacheKey($chainId));
    }

    public function makeChainCookie(string $chainId): Cookie
    {
        return Cookie::create(
            self::COOKIE_NAME,
            $chainId,
            now()->addSeconds((int) config('toolbar.request_data_ttl', 30)),
            '/',
        );
    }

    public function makeForgetCookie(): Cookie
    {
        return Cookie::create(
            self::COOKIE_NAME,
            '',
            now()->subMinute(),
            '/',
        );
    }

    private function cacheKey(string $chainId): string
    {
        return self::CACHE_PREFIX.$chainId;
    }

    private function normalizeHop(array $hop): array
    {
        return $this->normalizeValue($hop);
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeValue($item);
            }

            return $normalized;
        }

        if (
            is_int($value)
            || is_float($value)
            || is_bool($value)
            || is_string($value)
            || is_null($value)
        ) {
            return $value;
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return $this->normalizeValue($value->toArray());
        }

        if (is_object($value) && $value instanceof \JsonSerializable) {
            return $this->normalizeValue($value->jsonSerialize());
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return '';
    }
}
