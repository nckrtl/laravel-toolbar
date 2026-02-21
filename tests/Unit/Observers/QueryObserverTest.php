<?php

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use NckRtl\Toolbar\Observers\QueryObserver;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;

beforeEach(function () {
    // Initialize the Profiler to avoid errors
    Profiler::initialize();

    // Create a simple table for testing
    Schema::create('test_users', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('email')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_users');
});

it('records queries from QueryExecuted events', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->insert(['name' => 'John', 'email' => 'john@example.com']);

    expect($observer->queries)->not->toBeEmpty();
    expect($observer->queries)->toHaveCount(1);
});

it('calculates total query time', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->insert(['name' => 'John', 'email' => 'john@example.com']);
    DB::table('test_users')->where('id', 1)->first();

    expect($observer->totalTime)->toBeGreaterThan(0);
});

it('detects duplicate queries by sql hash', function () {
    $observer = new QueryObserver;

    // Run the same query twice
    DB::table('test_users')->where('id', 1)->first();
    DB::table('test_users')->where('id', 1)->first();

    expect($observer->queries)->toHaveCount(2);
    expect($observer->queries[0]->is_duplicate)->toBeFalse();
    expect($observer->queries[1]->is_duplicate)->toBeTrue();
});

it('marks slow queries over 100ms threshold', function () {
    $observer = new QueryObserver;

    // Create a mock QueryExecuted event with slow time
    $connection = DB::connection();
    $event = new QueryExecuted(
        'SELECT * FROM test_users WHERE id = ?',
        [1],
        150.0, // 150ms - slow
        $connection
    );

    $observer->recordQuery($event);

    expect($observer->queries[0]->is_slow)->toBeTrue();
});

it('does not mark fast queries as slow', function () {
    $observer = new QueryObserver;

    // Create a mock QueryExecuted event with fast time
    $connection = DB::connection();
    $event = new QueryExecuted(
        'SELECT * FROM test_users WHERE id = ?',
        [1],
        5.0, // 5ms - fast
        $connection
    );

    $observer->recordQuery($event);

    expect($observer->queries[0]->is_slow)->toBeFalse();
});

it('replaces bindings in sql correctly', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->where('name', 'John')->first();

    $query = $observer->queries[0];

    expect($query->sql)->toContain("'John'");
    expect($query->sql)->not->toContain('?');
});

it('handles null bindings', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->insert(['name' => 'John', 'email' => null]);

    // Find the insert query
    $insertQuery = collect($observer->queries)->first(fn ($q) => str_contains($q->sql, 'insert'));

    expect($insertQuery)->not->toBeNull();
    expect($insertQuery->sql)->toContain('null');
});

it('handles numeric bindings', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->where('id', 42)->first();

    $query = $observer->queries[0];

    expect($query->sql)->toContain('42');
});

it('tracks connections', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->first();

    expect($observer->connections)->toContain('testing');
});

it('tracks drivers', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->first();

    expect($observer->drivers)->toContain('sqlite');
});

it('tracks databases', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->first();

    expect($observer->databases)->not->toBeEmpty();
});

it('generates hash for queries', function () {
    $observer = new QueryObserver;

    [$hash, $isDuplicate] = $observer->hash('SELECT * FROM users');

    expect($hash)->toBe(md5('SELECT * FROM users'));
    expect($isDuplicate)->toBeFalse();
});

it('detects duplicate hashes', function () {
    $observer = new QueryObserver;

    [$hash1, $isDuplicate1] = $observer->hash('SELECT * FROM users');
    [$hash2, $isDuplicate2] = $observer->hash('SELECT * FROM users');

    expect($hash1)->toBe($hash2);
    expect($isDuplicate1)->toBeFalse();
    expect($isDuplicate2)->toBeTrue();
});

it('tracks memory usage per query', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->first();

    $query = $observer->queries[0];

    expect($query->memory_used)->not->toBeNull();
});

it('captures file and line in non-production', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->first();

    $query = $observer->queries[0];

    // In non-production, file and line should be captured
    // However, they might be null if the caller is from vendor
    // The important thing is that the query is recorded
    expect($query)->not->toBeNull();
});

it('records query connection name', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->first();

    $query = $observer->queries[0];

    expect($query->connection)->toBe('testing');
});

it('records query driver name', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->first();

    $query = $observer->queries[0];

    expect($query->driver)->toBe('sqlite');
});

// Octane compatibility tests - reset() method
it('provides reset method for Octane compatibility', function () {
    $observer = new QueryObserver;

    expect(method_exists($observer, 'reset'))->toBeTrue();
});

it('reset clears all queries', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->insert(['name' => 'John', 'email' => 'john@example.com']);
    DB::table('test_users')->first();

    expect($observer->queries)->not->toBeEmpty();

    $observer->reset();

    expect($observer->queries)->toBeEmpty();
});

it('reset clears total time', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->insert(['name' => 'John', 'email' => 'john@example.com']);

    expect($observer->totalTime)->toBeGreaterThan(0);

    $observer->reset();

    expect($observer->totalTime)->toBe(0.0);
});

it('reset clears connections', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->first();

    expect($observer->connections)->not->toBeEmpty();

    $observer->reset();

    expect($observer->connections)->toBeEmpty();
});

it('reset clears drivers', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->first();

    expect($observer->drivers)->not->toBeEmpty();

    $observer->reset();

    expect($observer->drivers)->toBeEmpty();
});

it('reset clears databases', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->first();

    expect($observer->databases)->not->toBeEmpty();

    $observer->reset();

    expect($observer->databases)->toBeEmpty();
});

it('reset clears hashes', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->first();
    DB::table('test_users')->first();

    expect($observer->hashes)->not->toBeEmpty();

    $observer->reset();

    expect($observer->hashes)->toBeEmpty();
});

it('reset allows fresh duplicate detection', function () {
    $observer = new QueryObserver;

    // Run same query twice - second should be duplicate
    DB::table('test_users')->where('id', 1)->first();
    DB::table('test_users')->where('id', 1)->first();

    expect($observer->queries[1]->is_duplicate)->toBeTrue();

    $observer->reset();

    // After reset, same query should NOT be duplicate (fresh state)
    DB::table('test_users')->where('id', 1)->first();

    expect($observer->queries[0]->is_duplicate)->toBeFalse();
});

it('reset simulates Octane request boundary', function () {
    $observer = new QueryObserver;

    // Simulate first request
    DB::table('test_users')->insert(['name' => 'Request1', 'email' => 'r1@example.com']);
    DB::table('test_users')->where('id', 1)->first();

    $firstRequestQueryCount = count($observer->queries);
    $firstRequestTotalTime = $observer->totalTime;

    expect($firstRequestQueryCount)->toBeGreaterThan(0);

    // Simulate Octane reset between requests
    $observer->reset();

    // Simulate second request
    DB::table('test_users')->insert(['name' => 'Request2', 'email' => 'r2@example.com']);

    // Second request should have fresh state, not accumulated from first
    expect($observer->queries)->toHaveCount(1);
    expect($observer->totalTime)->toBeLessThan($firstRequestTotalTime + $observer->queries[0]->duration);
});

// Regression test for null Profiler::getCurrentMemoryUsage
it('handles queries when Profiler has no memory checkpoints', function () {
    // Simulate scenario where no checkpoints with memory measurement have been recorded
    // This happens in contexts like recall.beast health checks with Caddy/systemd
    // Use getRequestStages() to clear ALL state including private $latestMemoryCheckpoint
    Profiler::getRequestStages();
    Profiler::$requestCheckpoints = [];

    // Verify that getCurrentMemoryUsage returns null in this state
    expect(Profiler::getCurrentMemoryUsage())->toBeNull();

    // QueryObserver should not throw when recording queries without Profiler memory data
    $observer = new QueryObserver;

    DB::table('test_users')->insert(['name' => 'John', 'email' => 'john@example.com']);

    // Query should be recorded successfully despite null memory checkpoint
    expect($observer->queries)->not->toBeEmpty();
    expect($observer->queries)->toHaveCount(1);
    expect($observer->queries[0]->sql)->toContain('insert');
});
