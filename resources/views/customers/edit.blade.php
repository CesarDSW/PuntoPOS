@extends('layout.dashboard_design')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/customers/edit.css') }}">
@endpush

@section('content')

<div class="customer-edit-page">
    <div class="page-header">
        <div>
            <h1>Editar cliente</h1>
            <p>Actualiza la información del cliente seleccionado.</p>
        </div>

        <a href="{{ route('customers') }}" class="btn-back">Volver</a>
    </div>

    @if($errors->any())
        <div class="error-box">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="edit-card">
        <h2>Datos del cliente</h2>

        <form method="POST" action="{{ route('customers.update', $customer->customer_id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nombre del cliente</label>
                <input
                    type="text"
                    name="name_customer"
                    class="form-input"
                    value="{{ old('name_customer', $customer->name_customer) }}"
                    required
                >
            </div>

            <div class="form-row-custom">
                <div class="form-group">
                    <label>Teléfono</label>
                    <input
                        type="text"
                        name="phone"
                        class="form-input"
                        value="{{ old('phone', $customer->phone) }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input
                        type="email"
                        name="email"
                        class="form-input"
                        value="{{ old('email', $customer->email) }}"
                        required
                    >
                </div>
            </div>

            <div class="actions">
                <a href="{{ route('customers') }}" class="btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center; padding:0 24px;">Cancelar</a>
                <button type="submit" class="btn-primary" style="padding:0 24px;">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection