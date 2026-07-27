@extends('layouts.panel')

@section('title', 'Editar local')

@section('content')
<div class="page-header">
    <div>
        <h2>Editar local</h2>

        <p>
            {{ $business->local_number }}
            — {{ $business->name }}
        </p>
    </div>
</div>

<div class="card">
    <form
        action="{{ route(
                'panel.locales.update',
                $business
            ) }}"
        method="POST">
        @csrf
        @method('PUT')

        @include('panel.businesses._form', [
        'submitText' => 'Actualizar local',
        ])
    </form>
</div>
@endsection