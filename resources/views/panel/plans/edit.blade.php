@extends('layouts.panel')

@section('title', 'Editar plan')

@section('content')
<div class="page-header">
    <div>
        <h2>Editar plan</h2>
        <p>{{ $plan->name }}</p>
    </div>
</div>

<div class="card">
    <form
        action="{{ route('panel.planes.update', $plan) }}"
        method="POST">
        @csrf
        @method('PUT')

        @include('panel.plans._form', [
        'submitText' => 'Actualizar plan',
        ])
    </form>
</div>
@endsection