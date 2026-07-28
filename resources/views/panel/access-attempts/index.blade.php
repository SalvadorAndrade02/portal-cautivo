@extends('layouts.panel')

@section('title', 'Intentos de acceso')

@section('content')
<div class="page-header">
    <div>
        <h2>Intentos de acceso</h2>

        <p>
            Historial de autenticaciones aceptadas y rechazadas.
        </p>
    </div>
</div>

<div class="card filter-card">
    <form method="GET">
        <div class="filters filters-access">
            <div>
                <label for="search">Buscar</label>

                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ request('search') }}"
                    placeholder="Usuario, IP, MAC o motivo">
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
                <label for="result">Resultado</label>

                <select id="result" name="result">
                    <option value="">Todos</option>

                    <option
                        value="accepted"
                        @selected(request('result')==='accepted' )>
                        Aceptados
                    </option>

                    <option
                        value="rejected"
                        @selected(request('result')==='rejected' )>
                        Rechazados
                    </option>
                </select>
            </div>

            <div>
                <label for="date_from">Desde</label>

                <input
                    id="date_from"
                    name="date_from"
                    type="date"
                    value="{{ request('date_from') }}">
            </div>

            <div>
                <label for="date_to">Hasta</label>

                <input
                    id="date_to"
                    name="date_to"
                    type="date"
                    value="{{ request('date_to') }}">
            </div>

            <div class="filter-actions">
                <button class="button" type="submit">
                    Filtrar
                </button>

                <a
                    class="button button-secondary"
                    href="{{ route('panel.intentos.index') }}">
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
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Local</th>
                <th>IP / MAC</th>
                <th>Resultado</th>
                <th>Motivo</th>
                <th>Origen</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($attempts as $attempt)
            <tr>
                <td>
                    {{ $attempt->attempted_at->format('d/m/Y H:i:s') }}
                </td>

                <td>
                    <strong>{{ $attempt->username }}</strong>

                    @if (!$attempt->portal_user_id)
                    <br>
                    <small>Usuario no identificado</small>
                    @endif
                </td>

                <td>
                    @if ($attempt->business)
                    {{ $attempt->business->local_number }}
                    <br>
                    <small>{{ $attempt->business->name }}</small>
                    @else
                    Sin local
                    @endif
                </td>

                <td>
                    {{ $attempt->ip_address ?? 'Sin IP' }}
                    <br>
                    <small>
                        {{ $attempt->mac_address ?? 'Sin MAC' }}
                    </small>
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
                    {{ $attempt->reason ?? 'Sin detalle' }}
                </td>

                <td>{{ ucfirst($attempt->source) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    Todavía no existen intentos registrados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @include('panel.partials.simple-pagination', [
    'paginator' => $attempts,
    ])
</div>
@endsection