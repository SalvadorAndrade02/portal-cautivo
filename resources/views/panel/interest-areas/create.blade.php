@extends('layouts.panel')

@section('title', 'Nueva área de interés')

@section('content')

<div class="page-header">
    <div>
        <h2>
            Nueva área de interés
        </h2>

        <p>
            Agrega una nueva opción para
            el formulario de visitantes.
        </p>
    </div>
</div>

<div class="card">

    <form
        action="{{ route(
            'panel.areas-interes.store'
        ) }}"
        method="POST">

        @csrf

        @include(
        'panel.interest-areas._form',
        [
        'submitText' =>
        'Crear área',
        ]
        )

    </form>

</div>

@endsection