<?php

use App\Http\Controllers\Panel\PlanController;
use Illuminate\Support\Facades\Route;

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
    });
