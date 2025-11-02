<?php

use NckRtl\Toolbar\Data\ProfileMarkerData;
use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Enums\TimeUnit;
use NckRtl\Toolbar\Measurement;

it('creates profile marker with label', function () {
    $marker = new ProfileMarkerData('Fetching articles');

    expect($marker->label)->toBe('Fetching articles');
});

it('auto-captures time when not provided', function () {
    $marker = new ProfileMarkerData('Test marker');

    expect($marker->time)->toBeInstanceOf(Measurement::class);
    expect($marker->time->unit)->toBe(TimeUnit::SECONDS);
    expect($marker->time->value)->toBeGreaterThan(0);
});

it('auto-captures memory when not provided', function () {
    $marker = new ProfileMarkerData('Test marker');

    expect($marker->memory_real)->toBeInstanceOf(Measurement::class);
    expect($marker->memory_real->unit)->toBe(DataSizeUnit::BYTES);
    expect($marker->memory_real->value)->toBeGreaterThan(0);
});

it('accepts custom time', function () {
    $customTime = new Measurement(1234.5678, TimeUnit::SECONDS);

    $marker = new ProfileMarkerData(
        label: 'Test marker',
        time: $customTime
    );

    expect($marker->time->value)->toBe(1234.5678);
});

it('accepts custom memory', function () {
    $customMemory = new Measurement(123456789, DataSizeUnit::BYTES);

    $marker = new ProfileMarkerData(
        label: 'Test marker',
        memory_real: $customMemory
    );

    expect($marker->memory_real->value)->toBe(123456789);
});

it('extends Spatie Data class', function () {
    $marker = new ProfileMarkerData('Test marker');

    expect($marker)->toBeInstanceOf(\Spatie\LaravelData\Data::class);
});
