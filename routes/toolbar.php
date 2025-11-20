<?php

use Illuminate\Support\Facades\Route;
use NckRtl\Toolbar\Controllers\AssetController;

Route::get('/_toolbar/{asset}', AssetController::class)
    ->name('toolbar.assets')
    ->middleware('web');
