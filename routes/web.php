<?php

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Panel\BusinessController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\PlanController;
use App\Http\Controllers\Panel\PortalUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Panel\DeviceController;
use App\Http\Controllers\Panel\AccessAttemptController;
use App\Http\Controllers\Panel\AccessSessionController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Public\VisitorRegistrationController;
use App\Http\Controllers\Panel\VisitorController;

Route::get('/', function () {
    return Auth::check()
        ? to_route('panel.dashboard')
        : to_route('login');
});

Route::controller(VisitorRegistrationController::class)
    ->prefix('wifi')
    ->name('wifi.')
    ->group(function (): void {
        Route::get(
            'registro',
            'create'
        )->name('register.create');

        Route::post(
            'registro',
            'store'
        )
            ->middleware('throttle:5,1')
            ->name('register.store');

        Route::get(
            'registro/exito',
            'success'
        )->name('register.success');
    });

Route::get(
    '/login',
    [AdminLoginController::class, 'create']
)->name('login');

Route::post(
    '/login',
    [AdminLoginController::class, 'store']
)
    ->middleware('throttle:5,1')
    ->name('login.store');

Route::post(
    '/logout',
    [AdminLoginController::class, 'destroy']
)
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')
    ->prefix('panel')
    ->name('panel.')
    ->group(function (): void {
        Route::get(
            '/',
            DashboardController::class
        )->name('dashboard');

        Route::resource(
            'planes',
            PlanController::class
        )
            ->parameters([
                'planes' => 'plan',
            ])
            ->except('show');

        Route::resource(
            'locales',
            BusinessController::class
        )
            ->parameters([
                'locales' => 'business',
            ])
            ->except('show');

        Route::resource(
            'usuarios',
            PortalUserController::class
        )
            ->parameters([
                'usuarios' => 'portalUser',
            ])
            ->except('show');
        Route::resource(
            'dispositivos',
            DeviceController::class
        )
            ->parameters([
                'dispositivos' => 'device',
            ])
            ->except('show');

        Route::get(
            'intentos',
            [AccessAttemptController::class, 'index']
        )->name('intentos.index');

        Route::get(
            'sesiones',
            [AccessSessionController::class, 'index']
        )->name('sesiones.index');

        Route::get(
            'visitantes',
            [VisitorController::class, 'index']
        )->name('visitantes.index');
    });
