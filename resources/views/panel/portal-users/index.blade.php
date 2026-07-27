@extends('layouts.panel')

@section('title', 'Usuarios del portal')

@section('content')
<div class="page-header">
    <div>
        <h2>Usuarios del portal</h2>

        <p>
            Administra las credenciales utilizadas para acceder a internet.
        </p>
    </div>

    <a
        class="button"
        href="{{ route('panel.usuarios.create') }}">
        Crear usuario
    </a>
</div>

<div class="card table-container">
    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Local</th>
                <th>Plan</th>
                <th>Estado del usuario</th>
                <th>Estado del local</th>
                <th>Último acceso</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($portalUsers as $portalUser)
            <tr>
                <td>
                    <strong>{{ $portalUser->username }}</strong>

                    @if ($portalUser->full_name)
                    <br>
                    <small>{{ $portalUser->full_name }}</small>
                    @endif
                </td>

                <td>
                    {{ $portalUser->business->local_number }}
                    <br>
                    <small>
                        {{ $portalUser->business->name }}
                    </small>
                </td>

                <td>
                    {{ $portalUser->business->plan?->name ?? 'Sin plan' }}
                </td>

                <td>
                    @if ($portalUser->status === 'active')
                    <span class="badge badge-active">
                        Activo
                    </span>
                    @else
                    <span class="badge badge-inactive">
                        {{ ucfirst($portalUser->status) }}
                    </span>
                    @endif
                </td>

                <td>
                    @if ($portalUser->business->status === 'active')
                    <span class="badge badge-active">
                        Activo
                    </span>
                    @else
                    <span class="badge badge-inactive">
                        {{ ucfirst(
                                    $portalUser->business->status
                                ) }}
                    </span>
                    @endif
                </td>

                <td>
                    {{ $portalUser->last_login_at
                            ?->format('d/m/Y H:i') ?? 'Sin accesos' }}
                </td>

                <td>
                    <div class="actions">
                        <a
                            class="button button-small"
                            href="{{ route(
                                    'panel.usuarios.edit',
                                    $portalUser
                                ) }}">
                            Editar
                        </a>

                        <form
                            action="{{ route(
                                    'panel.usuarios.destroy',
                                    $portalUser
                                ) }}"
                            method="POST"
                            onsubmit="return confirm(
                                    '¿Eliminar este usuario?'
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
                    No hay usuarios registrados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection