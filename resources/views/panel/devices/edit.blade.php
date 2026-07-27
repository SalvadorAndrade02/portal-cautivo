@extends('layouts.panel')

@section('title', 'Editar dispositivo')

@section('content')
<div class="page-header">
    <div>
        <h2>Editar dispositivo</h2>

        <p>
            {{ $device->name }}
            — {{ $device->mac_address }}
        </p>
    </div>
</div>

<div class="card">
    <form
        action="{{ route(
                'panel.dispositivos.update',
                $device
            ) }}"
        method="POST">
        @csrf
        @method('PUT')

        @include('panel.devices._form', [
        'submitText' => 'Actualizar dispositivo',
        ])
    </form>
</div>
@endsection