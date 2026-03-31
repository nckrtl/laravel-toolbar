<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use NckRtl\Toolbar\Controllers\AssetController;
use NckRtl\Toolbar\Controllers\HorizonController;
use NckRtl\Toolbar\Controllers\ProcessesController;
use NckRtl\Toolbar\Controllers\RequestDataController;

Route::prefix('_toolbar')->middleware(['web'])->group(function () {
    // Horizon control
    Route::prefix('horizon')->group(function () {
        Route::get('/status', [HorizonController::class, 'status']);
        Route::post('/start', [HorizonController::class, 'start']);
        Route::post('/stop', [HorizonController::class, 'stop']);
    });

    // Orbit processes
    Route::prefix('processes')->group(function () {
        Route::get('/status', [ProcessesController::class, 'status']);
    });

    Route::get('/requests/{requestId}', RequestDataController::class)->name('toolbar.requests.show');

    // Existing asset route
    Route::get('/{asset}', AssetController::class)->name('toolbar.assets');
});

// SSE stream — no middleware (no session locking, no CSRF, no buffering interference)
Route::get('_toolbar/processes/stream', [ProcessesController::class, 'stream']);
