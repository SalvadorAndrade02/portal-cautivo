@extends('layouts.panel')

@section('title', 'Crear dispositivo')

@section('content')
<div class="page-header">
    <div>
        <h2>Crear dispositivo</h2>

        <p>
            Registra una MAC y relaciónala con un local.
        </p>
    </div>
</div>

<div class="card">
    <form
        action="{{ route('panel.dispositivos.store') }}"
        method="POST">
        @csrf

        @include('panel.devices._form', [
        'submitText' => 'Guardar dispositivo',
        ])
    </form>
</div>
@endsection