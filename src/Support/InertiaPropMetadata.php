<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Support;

use Illuminate\Http\Request;
use Inertia\ResponseFactory;

/**
 * What each Inertia prop is: shared or page, its wrapper type (always, defer, optional,
 * merge, scroll, once) and where it is defined. Read from the payload Inertia's own
 * DevTools (inertia-laravel 3.3+) leaves on the request; older versions only tell which
 * props are shared.
 */
final class InertiaPropMetadata
{
    /** Request attribute Inertia\DevTools\RequestAttribute::PAYLOAD. */
    private const DEVTOOLS_PAYLOAD = 'inertia_devtools_payload';

    /**
     * @return array{props: array<string, array{shared: bool, type: ?string, defer_group: ?string, loaded: bool, source: ?array{file: string, line: int}}>, render_source: ?array{file: string, line: int}, component_path: ?string}|null
     */
    public static function forRequest(Request $request): ?array
    {
        $payload = $request->attributes->get(self::DEVTOOLS_PAYLOAD);

        if (is_array($payload) && is_array($payload['props'] ?? null)) {
            return self::fromDevTools($payload);
        }

        $shared = self::sharedKeys();

        if ($shared === []) {
            return null;
        }

        return [
            'props' => array_fill_keys($shared, self::prop(shared: true)),
            'render_source' => null,
            'component_path' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{props: array<string, array{shared: bool, type: ?string, defer_group: ?string, loaded: bool, source: ?array{file: string, line: int}}>, render_source: ?array{file: string, line: int}, component_path: ?string}
     */
    private static function fromDevTools(array $payload): array
    {
        $props = [];

        foreach ($payload['props'] as $path => $meta) {
            if (! is_string($path) || ! is_array($meta)) {
                continue;
            }

            $props[$path] = self::prop(
                shared: ($meta['shared'] ?? false) === true,
                type: is_string($meta['inertiaType'] ?? null) ? $meta['inertiaType'] : null,
                deferGroup: is_string($meta['deferGroup'] ?? null) ? $meta['deferGroup'] : null,
                source: self::source($meta['shareSource'] ?? null) ?? self::source($meta['renderSource'] ?? null),
            );
        }

        // Deferred props are not in the first response; the page lists them by group.
        $deferred = data_get($payload, 'responseBody.deferredProps');

        if (is_array($deferred)) {
            foreach ($deferred as $group => $keys) {
                foreach ((array) $keys as $key) {
                    if (is_string($key) && ! isset($props[$key])) {
                        $props[$key] = self::prop(
                            type: 'defer',
                            deferGroup: is_string($group) ? $group : null,
                            loaded: false,
                        );
                    }
                }
            }
        }

        return [
            'props' => $props,
            'render_source' => self::source($payload['renderSource'] ?? null),
            'component_path' => is_string($payload['componentPath'] ?? null) ? $payload['componentPath'] : null,
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function sharedKeys(): array
    {
        if (! class_exists(ResponseFactory::class) || ! app()->bound(ResponseFactory::class)) {
            return [];
        }

        try {
            $shared = app(ResponseFactory::class)->getShared();
        } catch (\Throwable) {
            return [];
        }

        if (! is_array($shared)) {
            return [];
        }

        return array_values(array_unique(array_map(
            fn (int|string $key): string => explode('.', (string) $key, 2)[0],
            array_keys($shared),
        )));
    }

    /**
     * @return array{shared: bool, type: ?string, defer_group: ?string, loaded: bool, source: ?array{file: string, line: int}}
     */
    private static function prop(
        bool $shared = false,
        ?string $type = null,
        ?string $deferGroup = null,
        bool $loaded = true,
        ?array $source = null,
    ): array {
        return [
            'shared' => $shared,
            'type' => $type,
            'defer_group' => $deferGroup,
            'loaded' => $loaded,
            'source' => $source,
        ];
    }

    /**
     * @return array{file: string, line: int}|null
     */
    private static function source(mixed $source): ?array
    {
        if (! is_array($source) || ! is_string($source['file'] ?? null) || ! is_numeric($source['line'] ?? null)) {
            return null;
        }

        return ['file' => $source['file'], 'line' => (int) $source['line']];
    }
}
