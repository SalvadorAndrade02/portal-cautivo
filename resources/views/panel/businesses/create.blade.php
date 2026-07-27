@extends('layouts.panel')

@section('title', 'Crear local')

@section('content')
<div class="page-header">
    <div>
        <h2>Crear local</h2>

        <p>
            Registra un comercio y asígnale un plan de internet.
        </p>
    </div>
</div>

<div class="card">
    <form
        action="{{ route('panel.locales.store') }}"
        method="POST">
        @csrf

        @include('panel.businesses._form', [
        'submitText' => 'Guardar local',
        ])
    </form>
</div>
@endsection