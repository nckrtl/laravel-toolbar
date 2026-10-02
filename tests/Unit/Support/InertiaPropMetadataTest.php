<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use NckRtl\Toolbar\Support\InertiaPropMetadata;

it('reads prop types, shared props and sources from the Inertia DevTools payload', function () {
    $request = Request::create('/');
    $request->attributes->set('inertia_devtools_payload', [
        'schemaVersion' => 1,
        'component' => 'Home',
        'props' => [
            'auth' => [
                'shared' => true,
                'inertiaType' => 'always',
                'shareSource' => ['file' => '/app/Http/Middleware/HandleInertiaRequests.php', 'line' => 42],
            ],
            'releases' => [
                'shared' => false,
                'inertiaType' => null,
                'renderSource' => ['file' => '/app/Http/Controllers/HomeController.php', 'line' => 21],
            ],
        ],
        'renderSource' => ['file' => '/app/Http/Controllers/HomeController.php', 'line' => 19],
        'componentPath' => '/resources/js/pages/Home.tsx',
        'responseBody' => ['deferredProps' => ['stats' => ['downloads']]],
    ]);

    expect(InertiaPropMetadata::forRequest($request))->toBe([
        'props' => [
            'auth' => [
                'shared' => true,
                'type' => 'always',
                'defer_group' => null,
                'loaded' => true,
                'source' => ['file' => '/app/Http/Middleware/HandleInertiaRequests.php', 'line' => 42],
            ],
            'releases' => [
                'shared' => false,
                'type' => null,
                'defer_group' => null,
                'loaded' => true,
                'source' => ['file' => '/app/Http/Controllers/HomeController.php', 'line' => 21],
            ],
            'downloads' => [
                'shared' => false,
                'type' => 'defer',
                'defer_group' => 'stats',
                'loaded' => false,
                'source' => null,
            ],
        ],
        'render_source' => ['file' => '/app/Http/Controllers/HomeController.php', 'line' => 19],
        'component_path' => '/resources/js/pages/Home.tsx',
    ]);
});

it('has no prop metadata without Inertia', function () {
    expect(InertiaPropMetadata::forRequest(Request::create('/')))->toBeNull();
});
