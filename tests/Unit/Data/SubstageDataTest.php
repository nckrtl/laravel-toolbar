<?php

use NckRtl\Toolbar\Data\ProfileMarkerData;
use NckRtl\Toolbar\Data\SubstageData;
use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Enums\TimeUnit;
use NckRtl\Toolbar\Measurement;

it('creates substage from two profile markers', function () {
    $startMarker = new ProfileMarkerData(
        label: 'Start',
        time: new Measurement(1000.0, TimeUnit::SECONDS),
        memory_real: new Measurement(100000, DataSizeUnit::BYTES)
    );

    $endMarker = new ProfileMarkerData(
        label: 'End',
        time: new Measurement(1000.5, TimeUnit::SECONDS),
        memory_real: new Measurement(150000, DataSizeUnit::BYTES)
    );

    $substage = new SubstageData(
        label: 'Test substage',
        start: $startMarker,
        end: $endMarker
    );

    expect($substage->label)->toBe('Test substage');
    expect($substage->start)->toBe($startMarker);
    expect($substage->end)->toBe($endMarker);
});

it('calculates wall time between markers', function () {
    $startMarker = new ProfileMarkerData(
        label: 'Start',
        time: new Measurement(1000.0, TimeUnit::SECONDS),
        memory_real: new Measurement(100000, DataSizeUnit::BYTES)
    );

    $endMarker = new ProfileMarkerData(
        label: 'End',
        time: new Measurement(1000.5, TimeUnit::SECONDS),
        memory_real: new Measurement(150000, DataSizeUnit::BYTES)
    );

    $substage = new SubstageData(
        label: 'Test substage',
        start: $startMarker,
        end: $endMarker
    );

    expect($substage->wall_time)->not->toBeNull();
    expect($substage->wall_time->measurement->unit)->toBe(TimeUnit::MILLISECONDS);
    expect($substage->wall_time->measurement->value)->toBe(500.0);
});

it('calculates memory delta between markers', function () {
    $startMarker = new ProfileMarkerData(
        label: 'Start',
        time: new Measurement(1000.0, TimeUnit::SECONDS),
        memory_real: new Measurement(100000, DataSizeUnit::BYTES)
    );

    $endMarker = new ProfileMarkerData(
        label: 'End',
        time: new Measurement(1000.5, TimeUnit::SECONDS),
        memory_real: new Measurement(150000, DataSizeUnit::BYTES)
    );

    $substage = new SubstageData(
        label: 'Test substage',
        start: $startMarker,
        end: $endMarker
    );

    expect($substage->memory_real_delta)->not->toBeNull();
    expect($substage->memory_real_delta->measurement->value)->toBe(50000.0);
});

it('handles negative memory delta', function () {
    $startMarker = new ProfileMarkerData(
        label: 'Start',
        time: new Measurement(1000.0, TimeUnit::SECONDS),
        memory_real: new Measurement(200000, DataSizeUnit::BYTES)
    );

    $endMarker = new ProfileMarkerData(
        label: 'End',
        time: new Measurement(1000.1, TimeUnit::SECONDS),
        memory_real: new Measurement(150000, DataSizeUnit::BYTES)
    );

    $substage = new SubstageData(
        label: 'Memory freed',
        start: $startMarker,
        end: $endMarker
    );

    expect($substage->memory_real_delta->measurement->value)->toBe(-50000.0);
});

it('calculates percentages relative to parent stage', function () {
    $startMarker = new ProfileMarkerData(
        label: 'Start',
        time: new Measurement(1000.0, TimeUnit::SECONDS),
        memory_real: new Measurement(100000, DataSizeUnit::BYTES)
    );

    $endMarker = new ProfileMarkerData(
        label: 'End',
        time: new Measurement(1000.5, TimeUnit::SECONDS),
        memory_real: new Measurement(200000, DataSizeUnit::BYTES)
    );

    $substage = new SubstageData(
        label: 'Test substage',
        start: $startMarker,
        end: $endMarker
    );

    $totalWallTime = new Measurement(1000.0, TimeUnit::MILLISECONDS);
    $totalMemory = new Measurement(200000, DataSizeUnit::BYTES);

    $substage->calculatePercentages($totalWallTime, $totalMemory);

    expect($substage->wall_time->percentage)->toBe(50.0);
    expect($substage->memory_real_delta->percentage)->toBe(50.0);
});
