@extends('layouts.panel')

@section('title', 'Locales')

@section('content')
<div class="page-header">
    <div>
        <h2>Locales</h2>

        <p>
            Administra comercios, planes y estados del servicio.
        </p>
    </div>

    <a
        class="button"
        href="{{ route('panel.locales.create') }}">
        Crear local
    </a>
</div>

<div class="card table-container">
    <table>
        <thead>
            <tr>
                <th>Local</th>
                <th>Responsable</th>
                <th>Plan</th>
                <th>Dispositivos</th>
                <th>Usuarios</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($businesses as $business)
            <tr>
                <td>
                    <strong>
                        {{ $business->local_number }}
                        — {{ $business->name }}
                    </strong>

                    @if ($business->address)
                    <br>

                    <small>
                        {{ $business->address }}
                    </small>
                    @endif
                </td>

                <td>
                    {{ $business->responsible_name ?? 'Sin responsable' }}

                    @if ($business->email)
                    <br>

                    <small>
                        {{ $business->email }}
                    </small>
                    @endif

                    @if ($business->phone)
                    <br>

                    <small>
                        {{ $business->phone }}
                    </small>
                    @endif
                </td>

                <td>
                    @if ($business->plan)
                    <strong>
                        {{ $business->plan->name }}
                    </strong>

                    @if (!$business->plan->active)
                    <br>

                    <span class="badge badge-inactive">
                        Plan inactivo
                    </span>
                    @endif
                    @else
                    Sin plan
                    @endif
                </td>

                <td>
                    {{ $business->effective_max_devices }}

                    @if ($business->max_devices !== null)
                    <br>

                    <small>
                        Límite particular
                    </small>
                    @elseif ($business->plan)
                    <br>

                    <small>
                        Definido por el plan
                    </small>
                    @endif
                </td>

                <td>
                    {{ $business->portal_users_count }}
                </td>

                <td>
                    @switch($business->status)
                    @case('active')
                    <span class="badge badge-active">
                        Activo
                    </span>
                    @break

                    @case('suspended')
                    <span class="badge badge-inactive">
                        Suspendido
                    </span>
                    @break

                    @case('cancelled')
                    <span class="badge badge-inactive">
                        Cancelado
                    </span>
                    @break
                    @endswitch
                </td>

                <td>
                    <div class="actions">
                        <a
                            class="button button-small"
                            href="{{ route(
                                    'panel.locales.edit',
                                    $business
                                ) }}">
                            Editar
                        </a>

                        <form
                            action="{{ route(
                                    'panel.locales.destroy',
                                    $business
                                ) }}"
                            method="POST"
                            onsubmit="return confirm(
                                    '¿Eliminar este local?'
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
                    No hay locales registrados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection