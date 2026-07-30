<?php

use App\Http\Controllers\Api\Internal\RadiusAccountingController;
use App\Http\Controllers\Api\Internal\RadiusAuthenticationController;
use Illuminate\Support\Facades\Route;

Route::prefix('internal/radius')
    ->middleware('radius.token')
    ->group(function (): void {
        Route::post(
            'authenticate',
            RadiusAuthenticationController::class
        )->name('radius.authenticate');

        Route::post(
            'accounting',
            RadiusAccountingController::class
        )->name('radius.accounting');
    });
