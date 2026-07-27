@extends('layouts.panel')

@section('title', 'Editar usuario')

@section('content')
<div class="page-header">
    <div>
        <h2>Editar usuario</h2>

        <p>{{ $portalUser->username }}</p>
    </div>
</div>

<div class="card">
    <form
        action="{{ route(
                'panel.usuarios.update',
                $portalUser
            ) }}"
        method="POST">
        @csrf
        @method('PUT')

        @include('panel.portal-users._form', [
        'submitText' => 'Actualizar usuario',
        ])
    </form>
</div>
@endsection