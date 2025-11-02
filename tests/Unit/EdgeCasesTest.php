<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use NckRtl\Toolbar\Tests\Helpers\MockResponse;
use NckRtl\Toolbar\Toolbar;
use NckRtl\Toolbar\ToolbarInjector;

beforeEach(function () {
    Toolbar::enable();
    Profiler::$requestCheckpoints = [];
    Profiler::$viewRenders = [];

    $toolbar = new Toolbar;
    $toolbar->config->collectors([]);
    $toolbar->config->enableInConsole();
    app()->instance(Toolbar::class, $toolbar);

    Route::get('/_toolbar/assets/{asset}', fn () => '')->name('toolbar.assets');
});

// Empty Response Body Tests

it('handles empty response body', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $response = MockResponse::make('');

    $injector->inject($request, $response);

    expect($response->getContent())->toBe('');
});

it('does not inject when content has no body tag', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $response = MockResponse::make('   ');

    $injector->inject($request, $response);

    expect($response->getContent())->not->toContain('laravel-toolbar-shadow-host');
});

// Content-Type Header Tests

it('injects when content-type header is missing but has body tag', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $response = MockResponse::make('<html><body>Test</body></html>');
    $response->headers->remove('Content-Type');

    $injector->inject($request, $response);

    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
});

it('handles response with charset in content-type', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $response = MockResponse::make('<html><body>Test</body></html>');
    $response->headers->set('Content-Type', 'text/html; charset=utf-8');

    $injector->inject($request, $response);

    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
});

it('injects for xhtml content type with body tag', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $response = MockResponse::make('<html><body>Test</body></html>');
    $response->headers->set('Content-Type', 'application/xhtml+xml');

    $injector->inject($request, $response);

    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
});

// Malformed HTML Tests

it('handles html without body tag', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $response = MockResponse::make('<html><div>No body tag here</div></html>');

    $injector->inject($request, $response);

    expect($response->getContent())->toBe('<html><div>No body tag here</div></html>');
    expect($response->getContent())->not->toContain('laravel-toolbar-shadow-host');
});

it('injects for uppercase BODY tag (case-insensitive)', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $response = MockResponse::make('<html><BODY>Test</BODY></html>');

    $injector->inject($request, $response);

    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
});

it('injects for mixed case Body tag (case-insensitive)', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $response = MockResponse::make('<html><Body>Test</Body></html>');

    $injector->inject($request, $response);

    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
});

it('handles multiple body tags - injects before last', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $response = MockResponse::make('<html><body>First</body><body>Second</body></html>');

    $injector->inject($request, $response);

    $content = $response->getContent();
    $lastBodyPos = strripos($content, '</body>');
    $toolbarPos = strpos($content, 'laravel-toolbar-shadow-host');

    expect($toolbarPos)->toBeLessThan($lastBodyPos);
});

// Large Response Tests

it('handles large html response', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $largeContent = '<html><body>'.str_repeat('Lorem ipsum dolor sit amet. ', 10000).'</body></html>';
    $response = MockResponse::make($largeContent);

    $injector->inject($request, $response);

    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
    expect(strlen($response->getContent()))->toBeGreaterThan(strlen($largeContent));
});

// Special Characters Tests

it('handles html with special characters', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $response = MockResponse::make('<html><body>Test with special chars: © ® ™ € £ ¥</body></html>');

    $injector->inject($request, $response);

    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
    expect($response->getContent())->toContain('© ® ™ € £ ¥');
});

it('handles html with unicode content', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $response = MockResponse::make('<html><body>Unicode: 你好世界 こんにちは 🚀 👋</body></html>');

    $injector->inject($request, $response);

    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
    expect($response->getContent())->toContain('你好世界');
    expect($response->getContent())->toContain('🚀');
});

// Request Method Tests

it('handles POST request', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'POST');
    $response = MockResponse::make('<html><body>POST response</body></html>');

    $injector->inject($request, $response);

    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
});

it('handles PUT request', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'PUT');
    $response = MockResponse::make('<html><body>PUT response</body></html>');

    $injector->inject($request, $response);

    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
});

it('handles DELETE request', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'DELETE');
    $response = MockResponse::make('<html><body>DELETE response</body></html>');

    $injector->inject($request, $response);

    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
});

// Response Status Code Tests

it('handles 404 response', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $response = MockResponse::make('<html><body>Not Found</body></html>', 404);

    $injector->inject($request, $response);

    expect($response->getStatusCode())->toBe(404);
    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
});

it('handles 500 response', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $response = MockResponse::make('<html><body>Server Error</body></html>', 500);

    $injector->inject($request, $response);

    expect($response->getStatusCode())->toBe(500);
    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
});

it('handles 301 redirect with html body', function () {
    $injector = new ToolbarInjector;
    $request = Request::create('/test', 'GET');
    $response = MockResponse::make('<html><body>Redirecting...</body></html>', 301);
    $response->headers->set('Location', '/new-location');

    $injector->inject($request, $response);

    expect($response->getStatusCode())->toBe(301);
    expect($response->getContent())->toContain('laravel-toolbar-shadow-host');
});

// CollectorManager Edge Cases

it('CollectorManager handles empty collectors', function () {
    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([]);

    $manager = new CollectorManager;
    $data = $manager->collectData();

    expect($data)->toBeArray();
    expect($data)->toHaveKey('metadata');
});

it('CollectorManager generates unique ids', function () {
    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([]);

    $manager1 = new CollectorManager;
    $data1 = $manager1->collectData();

    $manager2 = new CollectorManager;
    $data2 = $manager2->collectData();

    expect($data1['metadata']['id'])->not->toBe($data2['metadata']['id']);
});

// Profiler Edge Cases

it('Profiler handles getting stages with no checkpoints', function () {
    Profiler::$requestCheckpoints = [];

    $stages = Profiler::getRequestStages();

    expect($stages)->toBeArray();
});

// ResponseCollector Edge Cases

it('ResponseCollector handles null response gracefully', function () {
    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        new \NckRtl\Toolbar\Collectors\ResponseCollector,
    ]);

    $manager = new CollectorManager(response: null);
    $data = $manager->collectData();

    // Should not crash and should return null for response data
    expect($data)->toBeArray();
    expect($data['response'] ?? null)->toBeNull();
});

// QueriesCollector Edge Cases

it('QueriesCollector handles zero queries without division by zero', function () {
    $toolbar = app(Toolbar::class);
    $toolbar->config->collectors([
        new \NckRtl\Toolbar\Collectors\QueriesCollector,
    ]);

    // Ensure no queries have been recorded
    $queryObserver = $toolbar->config->getObserver(\NckRtl\Toolbar\Observers\QueryObserver::class);
    $queryObserver->reset();

    $manager = new CollectorManager;
    $data = $manager->collectData();

    // Should not throw division by zero error
    expect($data)->toBeArray();
    expect($data['queries'])->toBeInstanceOf(\NckRtl\Toolbar\Data\QueriesData::class);
    expect($data['queries']->queries)->toBeEmpty();
    expect($data['queries']->totalTimeFilteredQueries)->toBe(0.0);
});

// Profiler::getCurrentMemoryUsage Edge Cases

it('Profiler getCurrentMemoryUsage returns null with no memory checkpoints', function () {
    // Use getRequestStages() to clear ALL state including private $latestMemoryCheckpoint
    Profiler::getRequestStages();
    Profiler::$requestCheckpoints = [];

    $result = Profiler::getCurrentMemoryUsage();

    expect($result)->toBeNull();
});

it('Profiler getCurrentMemoryUsage returns null when no checkpoints have measureMemory true', function () {
    // Use getRequestStages() to clear ALL state including private $latestMemoryCheckpoint
    Profiler::getRequestStages();
    Profiler::$requestCheckpoints = [];

    // Record a checkpoint that doesn't measure memory
    Profiler::record(
        id: \NckRtl\Toolbar\Enums\RequestCheckpointId::LARAVEL_START,
        data: new \NckRtl\Toolbar\Data\RequestCheckpointData(
            time: new \NckRtl\Toolbar\Measurement(microtime(true), \NckRtl\Toolbar\Enums\TimeUnit::SECONDS),
            measureMemory: false,
        )
    );

    $result = Profiler::getCurrentMemoryUsage();

    expect($result)->toBeNull();
});

// TailwindCollector Edge Cases

it('TailwindCollector checks both vite plugin and main package', function () {
    $collector = new \NckRtl\Toolbar\Collectors\TailwindCollector;

    // This test verifies the collector doesn't crash
    // Actual version detection depends on node_modules being present
    $data = $collector->collectData(new CollectorManager);

    expect($data)->toBeInstanceOf(\NckRtl\Toolbar\Data\TailwindData::class);
});

// ToolbarInjector Manifest Edge Cases

it('ToolbarInjector handles missing manifest gracefully', function () {
    $injector = new class extends ToolbarInjector
    {
        public function testGetAssets(): array
        {
            return $this->getProductionManifestAssets();
        }
    };

    $assets = $injector->testGetAssets();

    // Even with missing manifest, should return array structure
    expect($assets)->toBeArray();
    expect($assets)->toHaveKey('js');
    expect($assets)->toHaveKey('css');
});
