<?php

use App\Http\Controllers\Panel\PlanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Panel\BusinessController;
use App\Http\Controllers\Panel\PortalUserController;

Route::redirect('/', '/panel/planes');

Route::prefix('panel')
    ->name('panel.')
    ->group(function (): void {
        Route::resource('planes', PlanController::class)
            ->parameters([
                'planes' => 'plan',
            ])
            ->except([
                'show',
            ]);
        Route::resource('locales', BusinessController::class)
            ->parameters([
                'locales' => 'business',
            ])
            ->except([
                'show',
            ]);
        Route::resource('usuarios', PortalUserController::class)
            ->parameters([
                'usuarios' => 'portalUser',
            ])
            ->except([
                'show',
            ]);
    });
