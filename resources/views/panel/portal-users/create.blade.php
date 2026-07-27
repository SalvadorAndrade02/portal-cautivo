@extends('layouts.panel')

@section('title', 'Crear usuario')

@section('content')
<div class="page-header">
    <div>
        <h2>Crear usuario del portal</h2>

        <p>
            Registra las credenciales asociadas a un local.
        </p>
    </div>
</div>

<div class="card">
    <form
        action="{{ route('panel.usuarios.store') }}"
        method="POST">
        @csrf

        @include('panel.portal-users._form', [
        'submitText' => 'Guardar usuario',
        ])
    </form>
</div>
@endsection