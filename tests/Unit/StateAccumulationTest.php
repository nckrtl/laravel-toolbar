<?php

/**
 * Tests for state accumulation issues that occur in long-running processes
 * like Laravel Octane (Swoole/RoadRunner) where the same PHP process handles
 * multiple requests without restarting.
 *
 * These tests verify that observers and the profiler properly reset their state
 * between requests to prevent data from one request leaking into another.
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use NckRtl\Toolbar\Observers\ModelObserver;
use NckRtl\Toolbar\Observers\QueryObserver;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;

beforeEach(function () {
    // Create a test table
    Schema::create('test_items', function ($table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_items');
});

describe('QueryObserver state accumulation', function () {
    it('accumulates queries when not reset (demonstrating the bug)', function () {
        // Simulate first request
        $observer = new QueryObserver;
        DB::table('test_items')->insert(['name' => 'Item 1']);
        $queriesAfterFirstRequest = count($observer->queries);

        // Simulate second request - REUSING THE SAME OBSERVER INSTANCE
        // This is what happens in Octane
        DB::table('test_items')->insert(['name' => 'Item 2']);
        $queriesAfterSecondRequest = count($observer->queries);

        // Bug: queries from first request are still present
        expect($queriesAfterSecondRequest)->toBeGreaterThan($queriesAfterFirstRequest);
        expect($queriesAfterSecondRequest)->toBe(2); // Both queries accumulated
    });

    it('accumulates totalTime when not reset (demonstrating the bug)', function () {
        $observer = new QueryObserver;

        // First request
        DB::table('test_items')->insert(['name' => 'Item 1']);
        $timeAfterFirstRequest = $observer->totalTime;

        // Second request - reusing same observer
        DB::table('test_items')->insert(['name' => 'Item 2']);
        $timeAfterSecondRequest = $observer->totalTime;

        // Bug: time accumulates across requests
        expect($timeAfterSecondRequest)->toBeGreaterThan($timeAfterFirstRequest);
    });

    it('accumulates hashes for duplicate detection (demonstrating the bug)', function () {
        $observer = new QueryObserver;

        // First request - query is NOT a duplicate
        DB::table('test_items')->where('id', 1)->first();
        expect($observer->queries[0]->is_duplicate)->toBeFalse();

        // Second request - same query appears as duplicate because hash is still stored
        DB::table('test_items')->where('id', 1)->first();
        expect($observer->queries[1]->is_duplicate)->toBeTrue(); // This is wrong!
    });

    it('should have a reset method that clears all state', function () {
        $observer = new QueryObserver;
        DB::table('test_items')->insert(['name' => 'Item 1']);

        expect($observer->queries)->toHaveCount(1);
        expect($observer->totalTime)->toBeGreaterThan(0);
        expect($observer->hashes)->not->toBeEmpty();

        // Reset method should exist and clear all state
        expect(method_exists($observer, 'reset'))->toBeTrue();

        $observer->reset();

        expect($observer->queries)->toBeEmpty();
        expect($observer->totalTime)->toBe(0.0);
        expect($observer->hashes)->toBeEmpty();
        expect($observer->connections)->toBeEmpty();
        expect($observer->drivers)->toBeEmpty();
        expect($observer->databases)->toBeEmpty();
    });

    it('does not carry state to next request after reset', function () {
        $observer = new QueryObserver;

        // First request
        DB::table('test_items')->insert(['name' => 'Item 1']);
        DB::table('test_items')->where('id', 1)->first();
        expect($observer->queries)->toHaveCount(2);

        // Simulate new request by resetting
        $observer->reset();

        // Second request
        DB::table('test_items')->where('id', 1)->first();

        // Should only have query from second request
        expect($observer->queries)->toHaveCount(1);
        // Query should NOT be marked as duplicate since hashes were cleared
        expect($observer->queries[0]->is_duplicate)->toBeFalse();
    });
});

describe('ModelObserver state accumulation', function () {
    it('accumulates hydration entries when not reset (demonstrating the bug)', function () {
        // We can't easily test this without a model class, but we can verify the structure
        $observer = new ModelObserver;

        expect($observer->hydrationEntries)->toBeArray();
        // After processing requests, this array would accumulate
    });

    it('should have a reset method that clears all state', function () {
        $observer = new ModelObserver;

        // Reset method should exist
        expect(method_exists($observer, 'reset'))->toBeTrue();

        $observer->reset();

        expect($observer->hydrationEntries)->toBeEmpty();
    });
});

describe('Profiler state accumulation', function () {
    beforeEach(function () {
        // Reset profiler state
        Profiler::$requestCheckpoints = [];
        Profiler::$viewRenders = [];
        Profiler::$profileMarkers = [];
    });

    it('clears checkpoints after getRequestStages', function () {
        Profiler::record(\NckRtl\Toolbar\Enums\RequestCheckpointId::BEFORE_MIDDLEWARE);

        expect(Profiler::$requestCheckpoints)->not->toBeEmpty();

        Profiler::getRequestStages();

        expect(Profiler::$requestCheckpoints)->toBeEmpty();
    });

    it('clears profile markers after getRequestStages', function () {
        Profiler::profile('Test marker');

        expect(Profiler::$profileMarkers)->toHaveCount(1);

        Profiler::getRequestStages();

        expect(Profiler::$profileMarkers)->toBeEmpty();
    });

    it('clears viewRenders after getRequestStages', function () {
        Profiler::$viewRenders['/path/to/view.blade.php'] = new \NckRtl\Toolbar\Data\RequestCheckpointData;

        expect(Profiler::$viewRenders)->toHaveCount(1);

        Profiler::getRequestStages();

        // Fixed: viewRenders is now cleared
        expect(Profiler::$viewRenders)->toBeEmpty();
    });

    it('initialize method resets all prior state', function () {
        // Add state from a "previous request"
        Profiler::record(\NckRtl\Toolbar\Enums\RequestCheckpointId::BEFORE_MIDDLEWARE);
        Profiler::$viewRenders['/path/to/view.blade.php'] = new \NckRtl\Toolbar\Data\RequestCheckpointData;
        Profiler::profile('Test');

        // Before initialize, we have state
        expect(Profiler::$requestCheckpoints)->toHaveKey('before_middleware');
        expect(Profiler::$viewRenders)->not->toBeEmpty();
        expect(Profiler::$profileMarkers)->not->toBeEmpty();

        // Initialize clears prior state (and may add new checkpoints like LARAVEL_START)
        Profiler::initialize();

        // Prior state should be cleared
        expect(Profiler::$requestCheckpoints)->not->toHaveKey('before_middleware');
        expect(Profiler::$viewRenders)->toBeEmpty();
        expect(Profiler::$profileMarkers)->toBeEmpty();
    });
});
