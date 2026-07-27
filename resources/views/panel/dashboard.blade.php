@extends('layouts.panel')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h2>Dashboard</h2>

        <p>
            Resumen general del portal cautivo.
        </p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <span>Planes registrados</span>
        <strong>{{ $stats['plans'] }}</strong>
        <small>
            {{ $stats['active_plans'] }} activos
        </small>
    </div>

    <div class="stat-card">
        <span>Locales registrados</span>
        <strong>{{ $stats['businesses'] }}</strong>
        <small>
            {{ $stats['active_businesses'] }} activos
        </small>
    </div>

    <div class="stat-card">
        <span>Locales suspendidos</span>
        <strong>
            {{ $stats['suspended_businesses'] }}
        </strong>
        <small>Sin servicio autorizado</small>
    </div>

    <div class="stat-card">
        <span>Usuarios del portal</span>
        <strong>{{ $stats['portal_users'] }}</strong>
        <small>
            {{ $stats['active_portal_users'] }} activos
        </small>
    </div>
</div>

<div class="card">
    <h3>Alertas de configuración</h3>

    <div class="warning-list">
        <div>
            <span>Locales sin plan asignado</span>

            <strong>
                {{ $alerts['businesses_without_plan'] }}
            </strong>
        </div>

        <div>
            <span>
                Usuarios asociados a locales no activos
            </span>

            <strong>
                {{ $alerts['users_with_inactive_business'] }}
            </strong>
        </div>

        <div>
            <span>
                Locales asociados a planes inactivos
            </span>

            <strong>
                {{ $alerts['businesses_with_inactive_plan'] }}
            </strong>
        </div>
    </div>
</div>
@endsection