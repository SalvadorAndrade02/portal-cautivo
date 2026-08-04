@extends('layouts.panel')

@section('title', 'Dashboard de visitantes')

@section('content')
<style>
    .dashboard-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .dashboard-heading h2 {
        margin: 0 0 7px;
        font-size: 28px;
    }

    .dashboard-heading p {
        margin: 0;
        color: #667085;
    }

    .dashboard-date {
        border: 1px solid #e3e8ef;
        border-radius: 10px;
        padding: 10px 14px;
        background: white;
        color: #475467;
        font-size: 14px;
    }

    .visitor-stats {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .visitor-stat {
        position: relative;
        overflow: hidden;
        min-height: 145px;
        border: 1px solid #e3e8ef;
        border-radius: 14px;
        padding: 20px;
        background: white;
        box-shadow:
            0 6px 20px rgba(16, 24, 40, .05);
    }

    .visitor-stat::before {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        content: "";
        background: #3157d5;
    }

    .visitor-stat.success::before {
        background: #12b76a;
    }

    .visitor-stat.warning::before {
        background: #f79009;
    }

    .visitor-stat.danger::before {
        background: #f04438;
    }

    .visitor-stat.purple::before {
        background: #7f56d9;
    }

    .stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .stat-label {
        color: #667085;
        font-size: 14px;
        font-weight: 600;
    }

    .stat-icon {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 10px;
        background: #f2f4f7;
        font-size: 18px;
    }

    .visitor-stat strong {
        display: block;
        margin-top: 18px;
        font-size: 32px;
        line-height: 1;
    }

    .visitor-stat small {
        display: block;
        margin-top: 9px;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 1.25fr) minmax(330px, .75fr);
        gap: 20px;
        margin-bottom: 22px;
    }

    .dashboard-card {
        border: 1px solid #e3e8ef;
        border-radius: 14px;
        padding: 22px;
        background: white;
        box-shadow:
            0 6px 20px rgba(16, 24, 40, .05);
    }

    .card-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
    }

    .card-heading h3 {
        margin: 0 0 5px;
        font-size: 18px;
    }

    .card-heading p {
        margin: 0;
        color: #667085;
        font-size: 14px;
    }

    .card-heading a {
        color: #3157d5;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
    }

    .registration-chart {
        display: grid;
        gap: 14px;
    }

    .chart-row {
        display: grid;
        grid-template-columns: 58px 1fr 35px;
        align-items: center;
        gap: 12px;
    }

    .chart-label {
        color: #667085;
        font-size: 13px;
        text-transform: capitalize;
    }

    .chart-track {
        overflow: hidden;
        height: 12px;
        border-radius: 999px;
        background: #f2f4f7;
    }

    .chart-bar {
        min-width: 3px;
        height: 100%;
        border-radius: 999px;
        background:
            linear-gradient(90deg,
                #3157d5,
                #7f8cff);
    }

    .chart-value {
        text-align: right;
        font-size: 13px;
        font-weight: bold;
    }

    .summary-list {
        display: grid;
        gap: 2px;
    }

    .summary-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        border-bottom: 1px solid #eef1f5;
        padding: 15px 0;
    }

    .summary-item:first-child {
        padding-top: 0;
    }

    .summary-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .summary-item span {
        color: #667085;
        font-size: 14px;
    }

    .summary-item strong {
        font-size: 18px;
    }

    .dashboard-tables {
        display: grid;
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .compact-list {
        display: grid;
    }

    .compact-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        border-bottom: 1px solid #eef1f5;
        padding: 14px 0;
    }

    .compact-item:first-child {
        padding-top: 0;
    }

    .compact-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .compact-main {
        min-width: 0;
    }

    .compact-main strong {
        display: block;
        overflow: hidden;
        margin-bottom: 5px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .compact-main small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .compact-meta {
        flex: 0 0 auto;
        text-align: right;
    }

    .compact-meta strong,
    .compact-meta small {
        display: block;
    }

    .empty-dashboard {
        border: 1px dashed #d0d5dd;
        border-radius: 10px;
        padding: 28px;
        text-align: center;
        color: #667085;
    }

    @media (max-width: 1050px) {
        .visitor-stats {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .dashboard-grid,
        .dashboard-tables {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 620px) {
        .dashboard-heading {
            align-items: flex-start;
            flex-direction: column;
        }

        .visitor-stats {
            grid-template-columns: 1fr;
        }

        .chart-row {
            grid-template-columns: 52px 1fr 30px;
        }
    }
</style>

<div class="dashboard-heading">
    <div>
        <h2>Resumen de visitantes</h2>

        <p>
            Actividad y registros generados mediante
            el portal cautivo.
        </p>
    </div>

    <div class="dashboard-date">
        {{ now()->locale('es')->translatedFormat(
            'l d \d\e F \d\e Y'
        ) }}
    </div>
</div>

<div class="visitor-stats">
    <div class="visitor-stat">
        <div class="stat-header">
            <span class="stat-label">
                Visitantes registrados
            </span>

            <span class="stat-icon">👥</span>
        </div>

        <strong>{{ number_format(
            $stats['total_visitors']
        ) }}</strong>

        <small>
            {{ number_format(
                $stats['visitors_today']
            ) }}
            registros nuevos hoy
        </small>
    </div>

    <div class="visitor-stat success">
        <div class="stat-header">
            <span class="stat-label">
                Sesiones activas
            </span>

            <span class="stat-icon">●</span>
        </div>

        <strong>{{ number_format(
            $stats['active_sessions']
        ) }}</strong>

        <small>
            {{ number_format(
                $stats['sessions_today']
            ) }}
            sesiones iniciadas hoy
        </small>
    </div>

    <div class="visitor-stat purple">
        <div class="stat-header">
            <span class="stat-label">
                Accesos aceptados hoy
            </span>

            <span class="stat-icon">✓</span>
        </div>

        <strong>{{ number_format(
            $stats['accepted_today']
        ) }}</strong>

        <small>
            Autenticaciones correctas de visitantes
        </small>
    </div>

    <div class="visitor-stat warning">
        <div class="stat-header">
            <span class="stat-label">
                Duración promedio
            </span>

            <span class="stat-icon">◷</span>
        </div>

        <strong>
            {{ number_format(
                $stats['average_session_minutes']
            ) }}
            min
        </strong>

        <small>
            Tiempo promedio por sesión finalizada
        </small>
    </div>
</div>

<div class="dashboard-grid">
    <section class="dashboard-card">
        <div class="card-heading">
            <div>
                <h3>Registros recientes</h3>

                <p>
                    Visitantes registrados durante
                    los últimos siete días.
                </p>
            </div>
        </div>

        <div class="registration-chart">
            @foreach ($registrationsByDay as $day)
            <div class="chart-row">
                <span class="chart-label">
                    {{ $day['label'] }}
                </span>

                <div class="chart-track">
                    <div
                        class="chart-bar"
                        style="width: {{
                            ($day['total']
                                / $maximumDailyRegistrations)
                                * 100
                        }}%">
                    </div>
                </div>

                <span class="chart-value">
                    {{ $day['total'] }}
                </span>
            </div>
            @endforeach
        </div>
    </section>

    <section class="dashboard-card">
        <div class="card-heading">
            <div>
                <h3>Estado general</h3>

                <p>
                    Situación actual de los registros.
                </p>
            </div>
        </div>

        <div class="summary-list">
            <div class="summary-item">
                <span>Visitantes activos</span>

                <strong>
                    {{ number_format(
                        $stats['active_visitors']
                    ) }}
                </strong>
            </div>

            <div class="summary-item">
                <span>Visitantes bloqueados</span>

                <strong>
                    {{ number_format(
                        $stats['blocked_visitors']
                    ) }}
                </strong>
            </div>

            <div class="summary-item">
                <span>Consentimientos comerciales</span>

                <strong>
                    {{ number_format(
                        $stats['marketing_consents']
                    ) }}
                </strong>
            </div>

            <div class="summary-item">
                <span>Registros realizados hoy</span>

                <strong>
                    {{ number_format(
                        $stats['visitors_today']
                    ) }}
                </strong>
            </div>
        </div>
    </section>
</div>

<div class="dashboard-tables">
    <section class="dashboard-card">
        <div class="card-heading">
            <div>
                <h3>Visitantes recientes</h3>

                <p>
                    Últimas personas registradas.
                </p>
            </div>

            <a href="{{ route(
                'panel.visitantes.index'
            ) }}">
                Ver todos
            </a>
        </div>

        @forelse ($recentVisitors as $visitor)
        <div class="compact-item">
            <div class="compact-main">
                <strong>
                    {{ $visitor->full_name }}
                </strong>

                <small>
                    {{ $visitor->email }}
                </small>

                <small>
                    {{ $visitor->phone }}
                </small>
            </div>

            <div class="compact-meta">
                <strong>
                    {{ $visitor->devices_count }}
                    disp.
                </strong>

                <small>
                    {{ $visitor->registered_at
                        ?->diffForHumans()
                        ?? 'Sin fecha' }}
                </small>
            </div>
        </div>
        @empty
        <div class="empty-dashboard">
            Todavía no existen visitantes registrados.
        </div>
        @endforelse
    </section>

    <section class="dashboard-card">
        <div class="card-heading">
            <div>
                <h3>Sesiones recientes</h3>

                <p>
                    Últimas conexiones de visitantes.
                </p>
            </div>

            <a href="{{ route(
                'panel.sesiones.index'
            ) }}">
                Ver todas
            </a>
        </div>

        @forelse ($recentSessions as $session)
        <div class="compact-item">
            <div class="compact-main">
                <strong>
                    {{ $session->visitor?->full_name
                        ?? 'Visitante sin identificar' }}
                </strong>

                <small>
                    {{ $session->ip_address
                        ?? 'Sin dirección IP' }}
                </small>

                <small>
                    {{ $session->mac_address
                        ?? $session->device?->mac_address
                        ?? 'Sin dirección MAC' }}
                </small>
            </div>

            <div class="compact-meta">
                @if ($session->status === 'active')
                <span class="badge badge-active">
                    Activa
                </span>
                @else
                <span class="badge badge-inactive">
                    {{ ucfirst($session->status) }}
                </span>
                @endif

                <small>
                    {{ $session->started_at
                        ?->diffForHumans()
                        ?? 'Sin fecha' }}
                </small>
            </div>
        </div>
        @empty
        <div class="empty-dashboard">
            Todavía no existen sesiones registradas.
        </div>
        @endforelse
    </section>
</div>
@endsection