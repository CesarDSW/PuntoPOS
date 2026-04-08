@extends('layout.dashboard_design')

@section('content')
<style>
    .customer-edit-page {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .page-header h1 {
        margin: 0 0 8px;
        font-size: 32px;
        color: #0f172a;
    }

    .page-header p {
        margin: 0;
        color: #64748b;
        font-size: 16px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 48px;
        padding: 0 20px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        background: #fff;
        color: #0f172a;
        text-decoration: none;
        font-weight: 600;
    }

    .edit-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 24px;
        max-width: 900px;
    }

    .edit-card h2 {
        margin: 0 0 20px;
        font-size: 18px;
        color: #0f172a;
    }

    .form-row-custom {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid #e5e7eb;
    }

    @media (max-width: 768px) {
        .form-row-custom {
            grid-template-columns: 1fr;
        }
    }
</style>

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