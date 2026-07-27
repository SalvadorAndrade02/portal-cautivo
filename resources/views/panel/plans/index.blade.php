@extends('layouts.panel')

@section('title', 'Planes de internet')

@section('content')
<div class="page-header">
    <div>
        <h2>Planes de internet</h2>

        <p>
            Administra velocidades, límites y duración de sesiones.
        </p>
    </div>

    <a
        class="button"
        href="{{ route('panel.planes.create') }}">
        Crear plan
    </a>
</div>

<div class="card table-container">
    <table>
        <thead>
            <tr>
                <th>Plan</th>
                <th>Velocidad</th>
                <th>Sesión</th>
                <th>Dispositivos</th>
                <th>Locales</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($plans as $plan)
            <tr>
                <td>
                    <strong>{{ $plan->name }}</strong>

                    @if ($plan->description)
                    <br>
                    <small>{{ $plan->description }}</small>
                    @endif
                </td>

                <td>
                    ↓ {{ $plan->download_speed_mbps ?? 'N/D' }} Mbps
                    <br>
                    ↑ {{ $plan->upload_speed_mbps ?? 'N/D' }} Mbps
                </td>

                <td>
                    {{ $plan->session_timeout_minutes }} min
                </td>

                <td>{{ $plan->max_devices }}</td>

                <td>{{ $plan->businesses_count }}</td>

                <td>
                    @if ($plan->active)
                    <span class="badge badge-active">
                        Activo
                    </span>
                    @else
                    <span class="badge badge-inactive">
                        Inactivo
                    </span>
                    @endif
                </td>

                <td>
                    <div class="actions">
                        <a
                            class="button button-small"
                            href="{{ route(
                                    'panel.planes.edit',
                                    $plan
                                ) }}">
                            Editar
                        </a>

                        <form
                            action="{{ route(
                                    'panel.planes.destroy',
                                    $plan
                                ) }}"
                            method="POST"
                            onsubmit="return confirm(
                                    '¿Eliminar este plan?'
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
                    No hay planes registrados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection