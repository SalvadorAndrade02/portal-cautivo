@extends('layouts.panel')

@section('title', 'Crear plan')

@section('content')
<div class="page-header">
    <div>
        <h2>Crear plan</h2>
        <p>Registra las reglas básicas del servicio.</p>
    </div>
</div>

<div class="card">
    <form
        action="{{ route('panel.planes.store') }}"
        method="POST">
        @csrf

        @include('panel.plans._form', [
        'submitText' => 'Guardar plan',
        ])
    </form>
</div>
@endsection