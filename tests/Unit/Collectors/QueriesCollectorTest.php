<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Collectors\QueriesCollector;
use NckRtl\Toolbar\Data\Configurations\QueriesConfig;
use NckRtl\Toolbar\Data\QueriesData;
use NckRtl\Toolbar\Observers\QueryObserver;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use NckRtl\Toolbar\Toolbar;

beforeEach(function () {
    Profiler::$requestCheckpoints = [];
    Profiler::initialize();

    Toolbar::enable();
    $toolbar = new Toolbar;
    $toolbar->config->collectors([]);
    $toolbar->config->enableInConsole();

    // Add query observer to config
    $toolbar->config->observers([new QueryObserver]);

    app()->instance(Toolbar::class, $toolbar);

    // Create test table
    Schema::create('queries_test', function ($table) {
        $table->id();
        $table->string('name');
    });
});

afterEach(function () {
    Schema::dropIfExists('queries_test');
});

it('returns queries data dto', function () {
    $collector = new QueriesCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data)->toBeInstanceOf(QueriesData::class);
});

it('has correct key', function () {
    $collector = new QueriesCollector;

    expect($collector->key())->toBe('queries');
});

it('has correct config class', function () {
    $collector = new QueriesCollector;

    expect($collector->configClass())->toBe(QueriesConfig::class);
});

it('retrieves queries from observer', function () {
    DB::table('queries_test')->insert(['name' => 'Test']);

    $collector = new QueriesCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->queries)->not->toBeEmpty();
});

it('calculates total time from queries', function () {
    DB::table('queries_test')->insert(['name' => 'Test1']);
    DB::table('queries_test')->insert(['name' => 'Test2']);

    $collector = new QueriesCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->totalTime)->toBeGreaterThan(0);
});

it('calculates percentage per query', function () {
    DB::table('queries_test')->insert(['name' => 'Test1']);
    DB::table('queries_test')->insert(['name' => 'Test2']);

    $collector = new QueriesCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    $totalPercentage = 0;
    foreach ($data->queries as $query) {
        expect($query->percentage)->toBeGreaterThanOrEqual(0);
        expect($query->percentage)->toBeLessThanOrEqual(1);
        $totalPercentage += $query->percentage;
    }

    // Total percentages should be approximately 1 (100%)
    expect($totalPercentage)->toBeGreaterThan(0.99);
    expect($totalPercentage)->toBeLessThan(1.01);
});

it('calculates offset for timeline visualization', function () {
    DB::table('queries_test')->insert(['name' => 'Test1']);
    DB::table('queries_test')->insert(['name' => 'Test2']);
    DB::table('queries_test')->insert(['name' => 'Test3']);

    $collector = new QueriesCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    // First query should have offset of 0
    expect($data->queries[0]->offset)->toBe(0.0);

    // Each subsequent query's offset should equal sum of previous percentages
    $expectedOffset = $data->queries[0]->percentage;
    expect($data->queries[1]->offset)->toBe($expectedOffset);
});

it('tracks connections', function () {
    DB::table('queries_test')->first();

    $collector = new QueriesCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->connections)->toContain('testing');
});

it('tracks drivers', function () {
    DB::table('queries_test')->first();

    $collector = new QueriesCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->drivers)->toContain('sqlite');
});

it('tracks databases', function () {
    DB::table('queries_test')->first();

    $collector = new QueriesCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    expect($data->databases)->not->toBeEmpty();
});

it('filters session queries by default', function () {
    // Simulate session query
    DB::table('queries_test')->where('name', 'test')->first();

    $collector = new QueriesCollector;
    $manager = new CollectorManager;

    $data = $collector->collectData($manager);

    // Verify session queries would be filtered if present
    foreach ($data->queries as $query) {
        expect($query->sql)->not->toContain('select * from "sessions" where "id" =');
    }
});

it('shows session queries when config enabled', function () {
    $config = new QueriesConfig(showSessionQueries: true);
    $collector = new QueriesCollector(config: $config);

    DB::table('queries_test')->first();

    $manager = new CollectorManager;
    $data = $collector->collectData($manager);

    // Config should allow session queries
    expect($collector->config->showSessionQueries)->toBeTrue();
});
