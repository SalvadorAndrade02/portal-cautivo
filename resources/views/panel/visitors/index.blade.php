@extends('layouts.panel')

@section('title', 'Visitantes')

@section('content')
<div class="page-header">
    <div>
        <h2>Visitantes</h2>

        <p>
            Personas registradas mediante el portal cautivo.
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
                    placeholder="Nombre, correo o teléfono">
            </div>

            <div>
                <label for="status">Estado</label>

                <select id="status" name="status">
                    <option value="">Todos</option>

                    <option
                        value="active"
                        @selected(request('status')==='active' )>
                        Activos
                    </option>

                    <option
                        value="blocked"
                        @selected(request('status')==='blocked' )>
                        Bloqueados
                    </option>

                    <option
                        value="disabled"
                        @selected(request('status')==='disabled' )>
                        Deshabilitados
                    </option>
                </select>
            </div>

            <div class="filter-actions">
                <button class="button" type="submit">
                    Filtrar
                </button>

                <a
                    class="button button-secondary"
                    href="{{ route('panel.visitantes.index') }}">
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
                <th>Visitante</th>
                <th>Intereses</th>
                <th>Estado</th>
                <th>Dispositivos</th>
                <th>Sesiones</th>
                <th>Credenciales</th>
                <th>Registro</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($visitors as $visitor)
            <tr>
                <td>
                    <strong>{{ $visitor->full_name }}</strong>

                    <br>

                    <small>{{ $visitor->email }}</small>

                    <br>

                    <small>{{ $visitor->phone }}</small>
                </td>

                <td>
                    @forelse ($visitor->interestAreas as $area)
                    <span class="badge">
                        {{ $area->name }}
                    </span>
                    @empty
                    <small>Sin intereses</small>
                    @endforelse
                </td>

                <td>
                    @if ($visitor->status === 'active')
                    <span class="badge badge-active">
                        Activo
                    </span>
                    @elseif ($visitor->status === 'blocked')
                    <span class="badge badge-inactive">
                        Bloqueado
                    </span>
                    @else
                    <span class="badge badge-inactive">
                        Deshabilitado
                    </span>
                    @endif
                </td>

                <td>
                    {{ $visitor->devices_count }}
                </td>

                <td>
                    {{ $visitor->access_sessions_count }}
                </td>

                <td>
                    {{ $visitor->access_tokens_count }}
                </td>

                <td>
                    {{ $visitor->registered_at
                            ?->format('d/m/Y H:i')
                            ?? 'Sin fecha' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    No hay visitantes registrados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if ($visitors->hasPages())
    <div class="pagination">
        @if ($visitors->onFirstPage())
        <span>Anterior</span>
        @else
        <a href="{{ $visitors->previousPageUrl() }}">
            Anterior
        </a>
        @endif

        <strong>
            Página {{ $visitors->currentPage() }}
            de {{ $visitors->lastPage() }}
        </strong>

        @if ($visitors->hasMorePages())
        <a href="{{ $visitors->nextPageUrl() }}">
            Siguiente
        </a>
        @else
        <span>Siguiente</span>
        @endif
    </div>
    @endif
</div>
@endsection