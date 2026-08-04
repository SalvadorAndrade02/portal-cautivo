@extends('layouts.panel')

@section('title', 'Dispositivos')

@section('content')
<div class="page-header">
    <div>
        <h2>Dispositivos</h2>

        <p>
            Administra direcciones MAC, bloqueos y autorizaciones.
        </p>
    </div>

    <a
        class="button"
        href="{{ route('panel.dispositivos.create') }}">
        Crear dispositivo
    </a>
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
                    placeholder="Nombre, MAC o IP">
            </div>

            <div>
                <label for="business_id">Local</label>

                <select
                    id="business_id"
                    name="business_id">
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

                    <option
                        value="authorized"
                        @selected(
                        request('status')==='authorized'
                        )>
                        Autorizados
                    </option>

                    <option
                        value="pending"
                        @selected(
                        request('status')==='pending'
                        )>
                        Pendientes
                    </option>

                    <option
                        value="blocked"
                        @selected(
                        request('status')==='blocked'
                        )>
                        Bloqueados
                    </option>

                    <option
                        value="bypass"
                        @selected(
                        request('status')==='bypass'
                        )>
                        Sin portal
                    </option>
                </select>
            </div>

            <div class="filter-actions">
                <button class="button" type="submit">
                    Filtrar
                </button>

                <a
                    class="button button-secondary"
                    href="{{ route(
                            'panel.dispositivos.index'
                        ) }}">
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
                <th>Dispositivo</th>
                <th>Asignación</th>
                <th>Responsable</th>
                <th>MAC / IP</th>
                <th>Acceso</th>
                <th>Última conexión</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($devices as $device)
            <tr>
                <td>
                    <strong>{{ $device->name }}</strong>
                    <br>
                    <small>{{ ucfirst($device->device_type) }}</small>
                </td>

                <td>
                    @if ($device->visitor)
                    <span class="badge badge-active">
                        Visitante
                    </span>

                    <br>

                    <small>
                        Registro del portal cautivo
                    </small>
                    @elseif ($device->business)
                    <strong>
                        Local {{ $device->business->local_number }}
                    </strong>

                    <br>

                    <small>
                        {{ $device->business->name }}
                    </small>
                    @else
                    <span class="badge badge-inactive">
                        Sin asignar
                    </span>
                    @endif
                </td>

                <td>
                    @if ($device->visitor)
                    <strong>
                        {{ $device->visitor->full_name }}
                    </strong>

                    <br>

                    <small>
                        {{ $device->visitor->email }}
                    </small>

                    <br>

                    <small>
                        {{ $device->visitor->phone }}
                    </small>
                    @elseif ($device->portalUser)
                    <strong>
                        {{ $device->portalUser->username }}
                    </strong>
                    @else
                    <small>Sin responsable</small>
                    @endif
                </td>

                <td>
                    <strong>{{ $device->mac_address }}</strong>
                    <br>
                    <small>
                        {{ $device->last_ip_address ?? 'Sin IP' }}
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
                    <span class="badge badge-inactive">
                        Pendiente
                    </span>
                    @endif

                    @if ($device->bypass_portal)
                    <br>
                    <small>Acceso sin portal</small>
                    @endif
                </td>

                <td>
                    {{ $device->last_seen_at
                            ?->format('d/m/Y H:i')
                            ?? 'Sin conexiones' }}
                </td>

                <td>
                    <div class="actions">
                        <a
                            class="button button-small"
                            href="{{ route(
                                    'panel.dispositivos.edit',
                                    $device
                                ) }}">
                            Editar
                        </a>

                        <form
                            action="{{ route(
                                    'panel.dispositivos.destroy',
                                    $device
                                ) }}"
                            method="POST"
                            onsubmit="return confirm(
                                    '¿Eliminar este dispositivo?'
                                )">
                            @csrf
                            @method('DELETE')

                            <button
                                class="button button-danger button-small"
                                type="submit">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    No hay dispositivos registrados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if ($devices->hasPages())
    <div class="pagination">
        @if ($devices->onFirstPage())
        <span>Anterior</span>
        @else
        <a href="{{ $devices->previousPageUrl() }}">
            Anterior
        </a>
        @endif

        <strong>
            Página {{ $devices->currentPage() }}
            de {{ $devices->lastPage() }}
        </strong>

        @if ($devices->hasMorePages())
        <a href="{{ $devices->nextPageUrl() }}">
            Siguiente
        </a>
        @else
        <span>Siguiente</span>
        @endif
    </div>
    @endif
</div>
@endsection