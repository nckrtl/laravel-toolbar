<?php

use NckRtl\Toolbar\Data\ModelData;
use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Measurement;
use NckRtl\Toolbar\Observers\ModelObserver;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;

beforeEach(function () {
    Profiler::$requestCheckpoints = [];
    Profiler::initialize();
});

it('has empty hydration entries on construction', function () {
    $observer = new ModelObserver;

    expect($observer->hydrationEntries)->toBeEmpty();
});

it('resets state correctly', function () {
    $observer = new ModelObserver;

    // Simulate some state
    $observer->hydrationEntries['App\\Models\\User'] = new ModelData(
        action: 'retrieved',
        model: 'App\\Models\\User',
        count: 5,
        memory_used: new Measurement(1024, DataSizeUnit::BYTES),
    );

    $observer->reset();

    expect($observer->hydrationEntries)->toBeEmpty();
});

it('resets memory tracking on reset', function () {
    $observer = new ModelObserver;

    // Record a hydration to set currentMemory
    $mockModel = new class
    {
    };
    $observer->recordHydrations(['model' => $mockModel], 1000, 2000);

    // Reset should clear memory tracking
    $observer->reset();

    expect($observer->hydrationEntries)->toBeEmpty();
});

it('records hydration entries for models', function () {
    $observer = new ModelObserver;

    $mockModel = new class
    {
    };
    $modelClass = get_class($mockModel);

    $observer->recordHydrations(['model' => $mockModel], 1000, 2000);

    expect($observer->hydrationEntries)->toHaveKey($modelClass);
    expect($observer->hydrationEntries[$modelClass])->toBeInstanceOf(ModelData::class);
});

it('increments count for duplicate model hydrations', function () {
    $observer = new ModelObserver;

    $mockModel = new class
    {
    };
    $modelClass = get_class($mockModel);

    $observer->recordHydrations(['model' => $mockModel], 1000, 2000);
    $observer->recordHydrations(['model' => $mockModel], 2000, 3000);
    $observer->recordHydrations(['model' => $mockModel], 3000, 4000);

    expect($observer->hydrationEntries[$modelClass]->count)->toBe(3);
});

it('tracks memory usage for model hydrations', function () {
    $observer = new ModelObserver;

    $mockModel = new class
    {
    };
    $modelClass = get_class($mockModel);

    $observer->recordHydrations(['model' => $mockModel], 1000, 2000);

    expect($observer->hydrationEntries[$modelClass]->memory_used)->toBeInstanceOf(Measurement::class);
    expect($observer->hydrationEntries[$modelClass]->memory_used->value)->toBe(1000);
});

it('sets action to retrieved for hydrations', function () {
    $observer = new ModelObserver;

    $mockModel = new class
    {
    };
    $modelClass = get_class($mockModel);

    $observer->recordHydrations(['model' => $mockModel], 1000, 2000);

    expect($observer->hydrationEntries[$modelClass]->action)->toBe('retrieved');
});

it('handles array data format for model', function () {
    $observer = new ModelObserver;

    $mockModel = new class
    {
    };
    $modelClass = get_class($mockModel);

    // Test array format with model at index 0
    $observer->recordHydrations([$mockModel], 1000, 2000);

    expect($observer->hydrationEntries)->toHaveKey($modelClass);
});

it('ignores non-retrieved events', function () {
    $observer = new ModelObserver;

    // The recordAction method should only process events matching *retrieved*
    // Test that other events are ignored
    $mockModel = new class
    {
    };

    // Directly call recordAction with a non-retrieved event
    $observer->recordAction('eloquent.created', ['model' => $mockModel]);

    expect($observer->hydrationEntries)->toBeEmpty();
});

it('processes retrieved events', function () {
    $observer = new ModelObserver;

    $mockModel = new class
    {
    };
    $modelClass = get_class($mockModel);

    // Ensure profiler has a memory checkpoint
    Profiler::record(\NckRtl\Toolbar\Enums\RequestCheckpointId::BEFORE_MIDDLEWARE);

    // Directly call recordAction with a retrieved event
    $observer->recordAction('eloquent.retrieved: '.$modelClass, ['model' => $mockModel]);

    expect($observer->hydrationEntries)->toHaveKey($modelClass);
});

// Octane compatibility tests
it('provides reset method for Octane compatibility', function () {
    $observer = new ModelObserver;

    expect(method_exists($observer, 'reset'))->toBeTrue();
});

it('reset clears all accumulated data between requests', function () {
    $observer = new ModelObserver;

    // Simulate first request
    $mockUser = new class
    {
    };
    $mockPost = new class
    {
    };

    $observer->recordHydrations(['model' => $mockUser], 1000, 2000);
    $observer->recordHydrations(['model' => $mockPost], 2000, 3000);

    expect($observer->hydrationEntries)->toHaveCount(2);

    // Simulate Octane reset between requests
    $observer->reset();

    expect($observer->hydrationEntries)->toBeEmpty();

    // Simulate second request
    $observer->recordHydrations(['model' => $mockUser], 1000, 2000);

    expect($observer->hydrationEntries)->toHaveCount(1);
});
