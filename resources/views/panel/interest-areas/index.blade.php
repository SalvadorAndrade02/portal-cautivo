@extends('layouts.panel')

@section('title', 'Áreas de interés')

@section('content')

<div class="page-header">
    <div>
        <h2>Áreas de interés</h2>

        <p>
            Administra las opciones mostradas
            a los visitantes y el contenido
            recomendado después de conectarse.
        </p>
    </div>

    <a
        class="button"
        href="{{ route(
            'panel.areas-interes.create'
        ) }}">
        Nueva área
    </a>
</div>

<div class="card table-container">

    <table>

        <thead>
            <tr>
                <th>Área</th>
                <th>Redirección</th>
                <th>Visitantes</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>

            @forelse (
            $interestAreas
            as $interestArea
            )

            <tr>

                <td>
                    <strong>
                        {{ $interestArea->name }}
                    </strong>

                    <br>

                    <small>
                        {{ $interestArea->slug }}
                    </small>

                    @if ($interestArea->description)
                    <br>

                    <small>
                        {{ $interestArea->description }}
                    </small>
                    @endif
                </td>

                <td>
                    @if ($interestArea->redirect_url)

                    <a
                        href="{{ $interestArea->redirect_url }}"
                        target="_blank"
                        rel="noopener noreferrer">

                        {{ Str::limit(
                                $interestArea->redirect_url,
                                45
                            ) }}

                    </a>

                    @else

                    <span class="badge badge-inactive">
                        Sin URL
                    </span>

                    @endif
                </td>

                <td>
                    {{ $interestArea->visitors_count }}
                </td>

                <td>

                    @if ($interestArea->active)

                    <span class="badge badge-active">
                        Activa
                    </span>

                    @else

                    <span class="badge badge-inactive">
                        Inactiva
                    </span>

                    @endif

                </td>

                <td>

                    <a
                        class="button button-small"
                        href="{{ route(
                            'panel.areas-interes.edit',
                            $interestArea
                        ) }}">
                        Editar
                    </a>

                </td>

            </tr>

            @empty

            <tr>
                <td colspan="5">
                    No hay áreas de interés
                    registradas.
                </td>
            </tr>

            @endforelse

        </tbody>

    </table>

</div>

@endsection