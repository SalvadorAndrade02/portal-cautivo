@extends('layouts.panel')

@section('title', 'Detalle del visitante')

@section('content')
@php
$formatDuration = static function (
?int $seconds
): string {
$seconds = max(0, (int) $seconds);

if ($seconds < 60) {
    return $seconds . ' segundos' ;
    }

    $hours=intdiv($seconds, 3600);
    $minutes=intdiv($seconds % 3600, 60);

    if ($hours> 0) {
    return $hours . ' h ' . $minutes . ' min';
    }

    return $minutes . ' minutos';
    };

    $reasonLabels = [
    'invalid_credentials' =>
    'Credencial incorrecta',

    'visitor_not_found' =>
    'Visitante no encontrado',

    'visitor_blocked' =>
    'Visitante bloqueado',

    'visitor_disabled' =>
    'Visitante deshabilitado',

    'visitor_token_expired' =>
    'Credencial vencida',

    'visitor_token_revoked' =>
    'Credencial revocada',

    'visitor_token_device_mismatch' =>
    'La credencial pertenece a otro dispositivo',

    'device_blocked' =>
    'Dispositivo bloqueado',

    'device_not_authorized' =>
    'Dispositivo no autorizado',

    'visitor_credentials_valid' =>
    'Credenciales válidas',

    'visitor_credentials_valid_device_reassigned' =>
    'Acceso válido con dispositivo reasignado',
    ];
    @endphp

    <style>
        .visitor-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }

        .visitor-header h2 {
            margin: 0 0 7px;
            font-size: 27px;
        }

        .visitor-header p {
            margin: 0;
            color: #667085;
        }

        .visitor-actions {
            display: flex;
            gap: 10px;
        }

        .detail-stats {
            display: grid;
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .detail-stat {
            border: 1px solid #e3e8ef;
            border-radius: 13px;
            padding: 19px;
            background: white;
            box-shadow:
                0 5px 16px rgba(16, 24, 40, .05);
        }

        .detail-stat span {
            color: #667085;
            font-size: 13px;
            font-weight: 600;
        }

        .detail-stat strong {
            display: block;
            margin-top: 13px;
            font-size: 27px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 22px;
        }

        .detail-card {
            border: 1px solid #e3e8ef;
            border-radius: 13px;
            padding: 22px;
            background: white;
            box-shadow:
                0 5px 16px rgba(16, 24, 40, .05);
        }

        .detail-card+.detail-card-full,
        .detail-card-full+.detail-card-full {
            margin-top: 22px;
        }

        .detail-card-full {
            border: 1px solid #e3e8ef;
            border-radius: 13px;
            padding: 22px;
            background: white;
            box-shadow:
                0 5px 16px rgba(16, 24, 40, .05);
        }

        .detail-card h3,
        .detail-card-full h3 {
            margin: 0 0 18px;
            font-size: 18px;
        }

        .detail-list {
            display: grid;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 18px;
            border-bottom: 1px solid #eef1f5;
            padding: 12px 0;
        }

        .detail-row:first-child {
            padding-top: 0;
        }

        .detail-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .detail-row span {
            color: #667085;
            font-size: 14px;
        }

        .detail-row strong {
            overflow-wrap: anywhere;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 18px;
        }

        .section-header h3 {
            margin: 0;
        }

        .empty-state {
            border: 1px dashed #d0d5dd;
            border-radius: 9px;
            padding: 25px;
            text-align: center;
            color: #667085;
        }

        .badge-neutral {
            background: #f2f4f7;
            color: #475467;
        }

        .badge-warning {
            background: #fef0c7;
            color: #b54708;
        }

        .status-line {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .data-secondary {
            display: block;
            margin-top: 4px;
        }

        @media (max-width: 950px) {

            .detail-stats,
            .detail-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 650px) {
            .visitor-header {
                flex-direction: column;
            }

            .detail-stats,
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .detail-row {
                grid-template-columns: 1fr;
                gap: 5px;
            }
        }
    </style>

    <div class="visitor-header">
        <div>
            <div class="status-line">
                <h2>{{ $visitor->full_name }}</h2>

                @if ($visitor->status === 'active')
                <span class="badge badge-active">
                    Activo
                </span>
                @elseif ($visitor->status === 'blocked')
                <span class="badge badge-inactive">
                    Bloqueado
                </span>
                @else
                <span class="badge badge-warning">
                    Deshabilitado
                </span>
                @endif
            </div>

            <p>
                Información, dispositivos y actividad
                registrada en el portal cautivo.
            </p>
        </div>

        <div class="visitor-actions">
            <a
                class="button button-secondary"
                href="{{ route(
                'panel.visitantes.index'
            ) }}">
                Volver a visitantes
            </a>
        </div>
    </div>

    <div class="detail-stats">
        <div class="detail-stat">
            <span>Dispositivos</span>

            <strong>
                {{ number_format($summary['devices']) }}
            </strong>
        </div>

        <div class="detail-stat">
            <span>Sesiones totales</span>

            <strong>
                {{ number_format($summary['sessions']) }}
            </strong>
        </div>

        <div class="detail-stat">
            <span>Sesiones activas</span>

            <strong>
                {{ number_format(
                $summary['active_sessions']
            ) }}
            </strong>
        </div>

        <div class="detail-stat">
            <span>Tiempo conectado</span>

            <strong>
                {{ $formatDuration(
                $summary['total_duration_seconds']
            ) }}
            </strong>
        </div>
    </div>

    <div class="detail-grid">
        <section class="detail-card">
            <h3>Información de contacto</h3>

            <div class="detail-list">
                <div class="detail-row">
                    <span>Nombre completo</span>
                    <strong>{{ $visitor->full_name }}</strong>
                </div>

                <div class="detail-row">
                    <span>Correo electrónico</span>
                    <strong>{{ $visitor->email }}</strong>
                </div>

                <div class="detail-row">
                    <span>Teléfono</span>
                    <strong>{{ $visitor->phone }}</strong>
                </div>

                <div class="detail-row">
                    <span>Fecha de registro</span>

                    <strong>
                        {{ $visitor->registered_at
                        ?->format('d/m/Y H:i')
                        ?? 'Sin información' }}
                    </strong>
                </div>

                <div class="detail-row">
                    <span>Última conexión</span>

                    <strong>
                        {{ $visitor->last_access_at
                        ?->format('d/m/Y H:i')
                        ?? $latestSession?->started_at
                            ?->format('d/m/Y H:i')
                        ?? 'Sin conexiones' }}
                    </strong>
                </div>
            </div>
        </section>

        <section class="detail-card">
            <h3>Consentimientos e intereses</h3>

            <div class="detail-list">
                <div class="detail-row">
                    <span>Aviso de privacidad</span>

                    <strong>
                        @if ($latestConsent?->privacy_accepted_at)
                        Aceptado el
                        {{ $latestConsent
                            ->privacy_accepted_at
                            ->format('d/m/Y H:i') }}
                        @else
                        Sin registro
                        @endif
                    </strong>
                </div>

                <div class="detail-row">
                    <span>Términos de uso</span>

                    <strong>
                        @if ($latestConsent?->terms_accepted_at)
                        Aceptados
                        @else
                        Sin registro
                        @endif
                    </strong>
                </div>

                <div class="detail-row">
                    <span>Promociones</span>

                    <strong>
                        {{ $latestConsent?->marketing_consent
                        ? 'Aceptó recibir promociones'
                        : 'No aceptó promociones' }}
                    </strong>
                </div>

                <div class="detail-row">
                    <span>Áreas de interés</span>

                    <strong>
                        @forelse (
                        $visitor->interestAreas
                        as $interestArea
                        )
                        <span class="badge badge-neutral">
                            {{ $interestArea->name }}
                        </span>
                        @empty
                        Sin intereses registrados
                        @endforelse
                    </strong>
                </div>
            </div>
        </section>
    </div>

    <section class="detail-card-full">
        <div class="section-header">
            <h3>Dispositivos relacionados</h3>

            <span class="badge badge-neutral">
                {{ $devices->count() }} dispositivos
            </span>
        </div>

        @if ($devices->isNotEmpty())
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Dispositivo</th>
                        <th>MAC / IP</th>
                        <th>Estado</th>
                        <th>Sesiones</th>
                        <th>Intentos</th>
                        <th>Última conexión</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($devices as $device)
                    <tr>
                        <td>
                            <strong>
                                {{ $device->name }}
                            </strong>

                            <small class="data-secondary">
                                {{ ucfirst(
                                        $device->device_type
                                    ) }}
                            </small>
                        </td>

                        <td>
                            <strong>
                                {{ $device->mac_address }}
                            </strong>

                            <small class="data-secondary">
                                {{ $device->last_ip_address
                                        ?? 'Sin IP' }}
                            </small>
                        </td>

                        <td>
                            @if ($device->blocked)
                            <span class="badge badge-inactive">
                                Bloqueado
                            </span>
                            @elseif ($device->authorized)
                            <span class="badge badge-active">
                                Autorizado
                            </span>
                            @else
                            <span class="badge badge-warning">
                                Pendiente
                            </span>
                            @endif
                        </td>

                        <td>
                            {{ $device
                                    ->access_sessions_count }}
                        </td>

                        <td>
                            {{ $device
                                    ->access_attempts_count }}
                        </td>

                        <td>
                            {{ $device->last_seen_at
                                    ?->format('d/m/Y H:i')
                                    ?? 'Sin registro' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            Este visitante no tiene dispositivos relacionados.
        </div>
        @endif
    </section>

    <section class="detail-card-full">
        <div class="section-header">
            <h3>Historial de sesiones</h3>

            <span class="badge badge-neutral">
                {{ $summary['sessions'] }} sesiones
            </span>
        </div>

        @if ($sessions->isNotEmpty())
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Inicio</th>
                        <th>Dispositivo</th>
                        <th>IP / MAC</th>
                        <th>Duración</th>
                        <th>Consumo</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($sessions as $session)
                    <tr>
                        <td>
                            {{ $session->started_at
                                    ?->format('d/m/Y H:i')
                                    ?? 'Sin fecha' }}
                        </td>

                        <td>
                            {{ $session->device?->name
                                    ?? 'Sin dispositivo' }}
                        </td>

                        <td>
                            <strong>
                                {{ $session->ip_address
                                        ?? 'Sin IP' }}
                            </strong>

                            <small class="data-secondary">
                                {{ $session->mac_address
                                        ?? $session->device
                                            ?->mac_address
                                        ?? 'Sin MAC' }}
                            </small>
                        </td>

                        <td>
                            {{ $formatDuration(
                                    $session->duration_seconds
                                ) }}
                        </td>

                        <td>
                            <small>
                                Entrada:
                                {{ number_format(
                                        $session->input_bytes
                                        ?? 0
                                    ) }}
                                bytes
                            </small>

                            <small class="data-secondary">
                                Salida:
                                {{ number_format(
                                        $session->output_bytes
                                        ?? 0
                                    ) }}
                                bytes
                            </small>
                        </td>

                        <td>
                            @if ($session->status === 'active')
                            <span class="badge badge-active">
                                Activa
                            </span>
                            @else
                            <span class="badge badge-neutral">
                                {{ ucfirst(
                                            $session->status
                                        ) }}
                            </span>
                            @endif

                            @if ($session->termination_reason)
                            <small class="data-secondary">
                                {{ $session
                                            ->termination_reason }}
                            </small>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($sessions->hasPages())
        <div class="pagination">
            @if ($sessions->onFirstPage())
            <span>Anterior</span>
            @else
            <a href="{{ $sessions
                        ->previousPageUrl() }}">
                Anterior
            </a>
            @endif

            <strong>
                Página {{ $sessions->currentPage() }}
                de {{ $sessions->lastPage() }}
            </strong>

            @if ($sessions->hasMorePages())
            <a href="{{ $sessions
                        ->nextPageUrl() }}">
                Siguiente
            </a>
            @else
            <span>Siguiente</span>
            @endif
        </div>
        @endif
        @else
        <div class="empty-state">
            Este visitante todavía no tiene sesiones.
        </div>
        @endif
    </section>

    <section class="detail-card-full">
        <div class="section-header">
            <h3>Intentos de acceso</h3>

            <div class="status-line">
                <span class="badge badge-active">
                    {{ $summary['accepted_attempts'] }}
                    aceptados
                </span>

                <span class="badge badge-inactive">
                    {{ $summary['rejected_attempts'] }}
                    rechazados
                </span>
            </div>
        </div>

        @if ($attempts->isNotEmpty())
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Resultado</th>
                        <th>Motivo</th>
                        <th>Dispositivo</th>
                        <th>IP / MAC</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($attempts as $attempt)
                    <tr>
                        <td>
                            {{ $attempt->attempted_at
                                    ?->format('d/m/Y H:i')
                                    ?? 'Sin fecha' }}
                        </td>

                        <td>
                            @if ($attempt->result === 'accepted')
                            <span class="badge badge-active">
                                Aceptado
                            </span>
                            @else
                            <span class="badge badge-inactive">
                                Rechazado
                            </span>
                            @endif
                        </td>

                        <td>
                            {{ $reasonLabels[
                                    $attempt->reason
                                ] ?? str_replace(
                                    '_',
                                    ' ',
                                    ucfirst(
                                        $attempt->reason
                                    )
                                ) }}
                        </td>

                        <td>
                            {{ $attempt->device?->name
                                    ?? 'Sin dispositivo' }}
                        </td>

                        <td>
                            <strong>
                                {{ $attempt->ip_address
                                        ?? 'Sin IP' }}
                            </strong>

                            <small class="data-secondary">
                                {{ $attempt->mac_address
                                        ?? $attempt->device
                                            ?->mac_address
                                        ?? 'Sin MAC' }}
                            </small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($attempts->hasPages())
        <div class="pagination">
            @if ($attempts->onFirstPage())
            <span>Anterior</span>
            @else
            <a href="{{ $attempts
                        ->previousPageUrl() }}">
                Anterior
            </a>
            @endif

            <strong>
                Página {{ $attempts->currentPage() }}
                de {{ $attempts->lastPage() }}
            </strong>

            @if ($attempts->hasMorePages())
            <a href="{{ $attempts
                        ->nextPageUrl() }}">
                Siguiente
            </a>
            @else
            <span>Siguiente</span>
            @endif
        </div>
        @endif
        @else
        <div class="empty-state">
            No hay intentos de acceso registrados.
        </div>
        @endif
    </section>

    <section class="detail-card-full">
        <div class="section-header">
            <h3>Credenciales temporales</h3>

            <span class="badge badge-neutral">
                {{ $tokens->count() }} credenciales
            </span>
        </div>

        @if ($tokens->isNotEmpty())
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Usuario interno</th>
                        <th>Dispositivo</th>
                        <th>Creación</th>
                        <th>Vencimiento</th>
                        <th>Último uso</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($tokens as $token)
                    <tr>
                        <td>
                            <code>
                                {{ $token->access_username }}
                            </code>
                        </td>

                        <td>
                            {{ $token->device?->name
                                    ?? 'Sin dispositivo' }}
                        </td>

                        <td>
                            {{ $token->created_at
                                    ?->format('d/m/Y H:i')
                                    ?? 'Sin fecha' }}
                        </td>

                        <td>
                            {{ $token->expires_at
                                    ?->format('d/m/Y H:i')
                                    ?? 'Sin vencimiento' }}
                        </td>

                        <td>
                            {{ $token->last_used_at
                                    ?->format('d/m/Y H:i')
                                    ?? 'No utilizada' }}
                        </td>

                        <td>
                            @if (
                            $token->status === 'active'
                            && !$token->revoked_at
                            && $token->expires_at
                            ?->isFuture()
                            )
                            <span class="badge badge-active">
                                Activa
                            </span>
                            @elseif ($token->revoked_at)
                            <span class="badge badge-inactive">
                                Revocada
                            </span>
                            @else
                            <span class="badge badge-warning">
                                Vencida
                            </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            No hay credenciales relacionadas.
        </div>
        @endif
    </section>
    @endsection