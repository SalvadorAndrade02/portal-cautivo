@extends('layouts.panel')

@section('title', 'Editar área de interés')

@section('content')

<div class="page-header">

    <div>

        <h2>
            Editar área de interés
        </h2>

        <p>
            Configura la categoría,
            prioridad y contenido recomendado.
        </p>

    </div>

</div>

<div class="card">

    <form
        action="{{ route(
            'panel.areas-interes.update',
            $interestArea
        ) }}"
        method="POST">

        @csrf
        @method('PUT')

        @include(
        'panel.interest-areas._form',
        [
        'submitText' =>
        'Guardar cambios',
        ]
        )

    </form>

</div>

@endsection