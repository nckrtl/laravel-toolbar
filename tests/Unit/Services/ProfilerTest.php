<?php

use NckRtl\Toolbar\Data\RequestCheckpointData;
use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Enums\RequestCheckpointId;
use NckRtl\Toolbar\Enums\TimeUnit;
use NckRtl\Toolbar\Measurement;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;

beforeEach(function () {
    // Use getRequestStages() to clear ALL state including private $latestMemoryCheckpoint
    // This is necessary because initialize() registers callbacks that fire immediately
    // in the test environment (since Laravel is already booted)
    Profiler::getRequestStages();

    // Ensure public arrays are clean for test isolation
    Profiler::$requestCheckpoints = [];
    Profiler::$viewRenders = [];
    Profiler::$profileMarkers = [];
});

it('records checkpoints', function () {
    Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE);

    expect(Profiler::$requestCheckpoints)->toHaveKey(RequestCheckpointId::BEFORE_MIDDLEWARE->value);
});

it('records checkpoints with timestamp', function () {
    Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE);

    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE);

    expect($checkpoint->time)->toBeInstanceOf(Measurement::class);
    expect($checkpoint->time->value)->toBeGreaterThan(0);
});

it('records checkpoints with memory', function () {
    Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE);

    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE);

    expect($checkpoint->memory_real)->toBeInstanceOf(Measurement::class);
    expect($checkpoint->memory_real->value)->toBeGreaterThan(0);
});

it('creates default checkpoint data when none provided', function () {
    Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE);

    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE);

    expect($checkpoint)->toBeInstanceOf(RequestCheckpointData::class);
});

it('accepts custom checkpoint data', function () {
    $customTime = microtime(true);
    $customMemory = 1234567;

    Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE, new RequestCheckpointData(
        memory_real: new Measurement($customMemory, DataSizeUnit::BYTES),
        time: new Measurement($customTime, TimeUnit::SECONDS)
    ));

    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE);

    expect($checkpoint->time->value)->toBe($customTime);
    expect($checkpoint->memory_real->value)->toBe($customMemory);
});

it('silently ignores duplicate checkpoint recordings', function () {
    $firstTime = microtime(true);

    Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE, new RequestCheckpointData(
        time: new Measurement($firstTime, TimeUnit::SECONDS)
    ));

    // Record again with different time - should be ignored
    $secondTime = microtime(true) + 1;
    Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE, new RequestCheckpointData(
        time: new Measurement($secondTime, TimeUnit::SECONDS)
    ));

    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE);

    // Should still have first time
    expect($checkpoint->time->value)->toBe($firstTime);
});

it('returns null for missing checkpoints', function () {
    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE);

    expect($checkpoint)->toBeNull();
});

it('gets checkpoint by id', function () {
    Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE);
    Profiler::record(RequestCheckpointId::AFTER_MIDDLEWARE);

    $beforeCheckpoint = Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE);
    $afterCheckpoint = Profiler::getCheckpoint(RequestCheckpointId::AFTER_MIDDLEWARE);

    expect($beforeCheckpoint)->not->toBeNull();
    expect($afterCheckpoint)->not->toBeNull();
    expect($beforeCheckpoint)->not->toBe($afterCheckpoint);
});

it('returns null for first view render when no views rendered', function () {
    $firstView = Profiler::getFirstViewRender();

    expect($firstView)->toBeNull();
});

it('returns null for last view render when no views rendered', function () {
    $lastView = Profiler::getLastViewRender();

    expect($lastView)->toBeNull();
});

it('tracks view renders', function () {
    // Simulate view render by directly adding to viewRenders
    Profiler::$viewRenders['/path/to/view.blade.php'] = new RequestCheckpointData;

    expect(Profiler::$viewRenders)->toHaveCount(1);
    expect(Profiler::$viewRenders)->toHaveKey('/path/to/view.blade.php');
});

it('gets first view render', function () {
    Profiler::$viewRenders['/path/to/first.blade.php'] = new RequestCheckpointData;
    Profiler::$viewRenders['/path/to/second.blade.php'] = new RequestCheckpointData;

    $firstView = Profiler::getFirstViewRender();

    expect($firstView)->toBeInstanceOf(RequestCheckpointData::class);
});

it('gets last view render', function () {
    Profiler::$viewRenders['/path/to/first.blade.php'] = new RequestCheckpointData;
    Profiler::$viewRenders['/path/to/second.blade.php'] = new RequestCheckpointData;

    $lastView = Profiler::getLastViewRender();

    expect($lastView)->toBeInstanceOf(RequestCheckpointData::class);
});

it('gets current memory usage from latest checkpoint with memory', function () {
    Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE, new RequestCheckpointData(
        memory_real: new Measurement(1000000, DataSizeUnit::BYTES),
        measureMemory: true
    ));

    $memory = Profiler::getCurrentMemoryUsage();

    expect($memory)->toBeInstanceOf(Measurement::class);
    expect($memory->value)->toBe(1000000);
});

it('returns null when no checkpoints with memory exist', function () {
    // Record checkpoint without memory
    Profiler::record(RequestCheckpointId::LARAVEL_START, new RequestCheckpointData(
        time: new Measurement(microtime(true), TimeUnit::SECONDS),
        measureMemory: false
    ));

    expect(Profiler::getCurrentMemoryUsage())->toBeNull();
});

it('returns request stages array', function () {
    // Record minimal checkpoints
    $baseTime = microtime(true);

    Profiler::record(RequestCheckpointId::LARAVEL_START, new RequestCheckpointData(
        time: new Measurement($baseTime, TimeUnit::SECONDS),
        measureMemory: false
    ));
    Profiler::record(RequestCheckpointId::BEFORE_SERVICES_PROVIDERS, new RequestCheckpointData(
        time: new Measurement($baseTime + 0.001, TimeUnit::SECONDS)
    ));

    $stages = Profiler::getRequestStages();

    expect($stages)->toBeArray();
});

it('clears checkpoints after getting request stages', function () {
    Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE);

    expect(Profiler::$requestCheckpoints)->not->toBeEmpty();

    Profiler::getRequestStages();

    expect(Profiler::$requestCheckpoints)->toBeEmpty();
});

it('can record checkpoint without memory measurement', function () {
    Profiler::record(RequestCheckpointId::LARAVEL_START, new RequestCheckpointData(
        time: new Measurement(microtime(true), TimeUnit::SECONDS),
        measureMemory: false
    ));

    $checkpoint = Profiler::getCheckpoint(RequestCheckpointId::LARAVEL_START);

    expect($checkpoint->measureMemory)->toBeFalse();
    expect($checkpoint->memory_real)->toBeNull();
});

it('stores checkpoints by their enum ProfilerTest', function () {
    Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE);

    expect(array_keys(Profiler::$requestCheckpoints))->toContain('before_middleware');
});

// Profile marker tests
it('records profile markers', function () {
    Profiler::profile('Fetching articles');

    expect(Profiler::$profileMarkers)->toHaveCount(1);
});

it('records profile marker with label', function () {
    Profiler::profile('Fetching articles');

    expect(Profiler::$profileMarkers[0]->label)->toBe('Fetching articles');
});

it('records profile marker with time', function () {
    Profiler::profile('Test marker');

    expect(Profiler::$profileMarkers[0]->time)->toBeInstanceOf(Measurement::class);
    expect(Profiler::$profileMarkers[0]->time->value)->toBeGreaterThan(0);
});

it('records profile marker with memory', function () {
    Profiler::profile('Test marker');

    expect(Profiler::$profileMarkers[0]->memory_real)->toBeInstanceOf(Measurement::class);
    expect(Profiler::$profileMarkers[0]->memory_real->value)->toBeGreaterThan(0);
});

it('records multiple profile markers', function () {
    Profiler::profile('First marker');
    Profiler::profile('Second marker');
    Profiler::profile('Third marker');

    expect(Profiler::$profileMarkers)->toHaveCount(3);
    expect(Profiler::$profileMarkers[0]->label)->toBe('First marker');
    expect(Profiler::$profileMarkers[1]->label)->toBe('Second marker');
    expect(Profiler::$profileMarkers[2]->label)->toBe('Third marker');
});

it('gets profile markers', function () {
    Profiler::profile('Test marker');

    $markers = Profiler::getProfileMarkers();

    expect($markers)->toHaveCount(1);
    expect($markers[0]->label)->toBe('Test marker');
});

it('clears profile markers', function () {
    Profiler::profile('Test marker');

    expect(Profiler::$profileMarkers)->toHaveCount(1);

    Profiler::clearProfileMarkers();

    expect(Profiler::$profileMarkers)->toBeEmpty();
});

it('clears profile markers after getting request stages', function () {
    Profiler::profile('Test marker');

    expect(Profiler::$profileMarkers)->toHaveCount(1);

    Profiler::getRequestStages();

    expect(Profiler::$profileMarkers)->toBeEmpty();
});

it('returns profile markers with request stages', function () {
    $baseTime = microtime(true);

    Profiler::record(RequestCheckpointId::LARAVEL_START, new RequestCheckpointData(
        time: new Measurement($baseTime, TimeUnit::SECONDS),
        measureMemory: false
    ));

    Profiler::profile('Test marker');

    [$stages, $markers] = Profiler::getRequestStages();

    expect($stages)->toBeArray();
    expect($markers)->toBeArray();
    expect($markers)->toHaveCount(1);
});
