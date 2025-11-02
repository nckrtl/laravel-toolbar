<?php

use Illuminate\Support\Facades\Route;
use NckRtl\Toolbar\Controllers\AssetController;
use NckRtl\Toolbar\Controllers\HorizonController;

Route::prefix('_toolbar')->middleware(['web'])->group(function () {
    // Existing asset route
    Route::get('/{asset}', AssetController::class)->name('toolbar.assets');

    // New: Horizon control
    Route::prefix('horizon')->group(function () {
        Route::get('/status', [HorizonController::class, 'status']);
        Route::post('/start', [HorizonController::class, 'start']);
        Route::post('/stop', [HorizonController::class, 'stop']);
    });
});
