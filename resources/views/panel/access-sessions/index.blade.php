@extends('layouts.panel')

@section('title', 'Sesiones de acceso')

@section('content')
<div class="page-header">
    <div>
        <h2>Sesiones de acceso</h2>

        <p>
            Consulta conexiones activas y el historial de navegación.
        </p>
    </div>
</div>

<div class="card filter-card">
    <form method="GET">
        <div class="filters">
            <div>
                <label for="search">Buscar</label>

                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ request('search') }}"
                    placeholder="Usuario, sesión, IP o MAC">
            </div>

            <div>
                <label for="business_id">Local</label>

                <select id="business_id" name="business_id">
                    <option value="">Todos</option>

                    @foreach ($businesses as $business)
                    <option
                        value="{{ $business->id }}"
                        @selected(
                        (string) request('business_id')===(string) $business->id
                        )
                        >
                        {{ $business->local_number }}
                        — {{ $business->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status">Estado</label>

                <select id="status" name="status">
                    <option value="">Todos</option>
                    <option value="active">Activa</option>
                    <option value="closed">Cerrada</option>
                    <option value="expired">Expirada</option>
                    <option value="disconnected">Desconectada</option>
                </select>
            </div>

            <div class="filter-actions">
                <button class="button" type="submit">
                    Filtrar
                </button>

                <a
                    class="button button-secondary"
                    href="{{ route('panel.sesiones.index') }}">
                    Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<div class="card table-container">
    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Local</th>
                <th>IP / MAC</th>
                <th>Inicio</th>
                <th>Duración</th>
                <th>Consumo</th>
                <th>Estado</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($sessions as $session)
            <tr>
                <td>
                    <strong>{{ $session->username }}</strong>
                    <br>
                    <small>{{ $session->radius_session_id }}</small>
                </td>

                <td>
                    @if ($session->business)
                    {{ $session->business->local_number }}
                    <br>
                    <small>{{ $session->business->name }}</small>
                    @else
                    Sin local
                    @endif
                </td>

                <td>
                    {{ $session->ip_address ?? 'Sin IP' }}
                    <br>
                    <small>
                        {{ $session->mac_address ?? 'Sin MAC' }}
                    </small>
                </td>

                <td>
                    {{ $session->started_at->format('d/m/Y H:i') }}

                    @if ($session->ended_at)
                    <br>
                    <small>
                        Fin:
                        {{ $session->ended_at->format('d/m/Y H:i') }}
                    </small>
                    @endif
                </td>

                <td>
                    {{ intdiv($session->duration_seconds, 3600) }} h
                    {{ intdiv(
                            $session->duration_seconds % 3600,
                            60
                        ) }} min
                </td>

                <td>
                    Entrada:
                    {{ number_format(
                            $session->input_bytes / 1048576,
                            2
                        ) }} MB
                    <br>
                    Salida:
                    {{ number_format(
                            $session->output_bytes / 1048576,
                            2
                        ) }} MB
                </td>

                <td>
                    @if ($session->status === 'active')
                    <span class="badge badge-active">
                        Activa
                    </span>
                    @else
                    <span class="badge badge-inactive">
                        {{ ucfirst($session->status) }}
                    </span>
                    @endif

                    @if ($session->termination_reason)
                    <br>
                    <small>
                        {{ $session->termination_reason }}
                    </small>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    Todavía no existen sesiones registradas.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @include('panel.partials.simple-pagination', [
    'paginator' => $sessions,
    ])
</div>
@endsection