<?php

use NckRtl\Toolbar\Services\ProfilerService\Profiler;

beforeEach(function () {
    Profiler::$requestCheckpoints = [];
    Profiler::$viewRenders = [];
    Profiler::$profileMarkers = [];
});

it('global profile function exists', function () {
    expect(function_exists('profile'))->toBeTrue();
});

it('global profile function records marker', function () {
    profile('Test marker');

    expect(Profiler::$profileMarkers)->toHaveCount(1);
    expect(Profiler::$profileMarkers[0]->label)->toBe('Test marker');
});

it('global profile function records multiple markers', function () {
    profile('First operation');
    profile('Second operation');
    profile('Third operation');

    expect(Profiler::$profileMarkers)->toHaveCount(3);
});

it('global profile function records timing and memory', function () {
    profile('Test operation');

    $marker = Profiler::$profileMarkers[0];

    expect($marker->time)->not->toBeNull();
    expect($marker->time->value)->toBeGreaterThan(0);
    expect($marker->memory_real)->not->toBeNull();
    expect($marker->memory_real->value)->toBeGreaterThan(0);
});
