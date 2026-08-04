<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use NckRtl\Toolbar\Controllers\AssetController;
use NckRtl\Toolbar\Controllers\HorizonController;
use NckRtl\Toolbar\Controllers\RequestDataController;

Route::prefix('_toolbar')->middleware(['web'])->group(function () {
    // Horizon control
    Route::prefix('horizon')->group(function () {
        Route::get('/status', [HorizonController::class, 'status']);
        Route::post('/start', [HorizonController::class, 'start']);
        Route::post('/stop', [HorizonController::class, 'stop']);
    });

    Route::get('/requests/{requestId}', RequestDataController::class)->name('toolbar.requests.show');

    // Existing asset route
    Route::get('/{asset}', AssetController::class)->name('toolbar.assets');
});
