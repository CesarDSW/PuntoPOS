@extends('layout.dashboard_design')

@section('content')
    <div class="customer-page">
        <div class="customer-header">
            <div>
                <h1>Clientes</h1>
                <p>Administra la base de clientes para tu negocio.</p>
            </div>    

            <button type="button" class="btn-primary" onClick="openCustomerModal()"> 
                + Nuevo Cliente
            </button>
        </div>
        
        @if(session('success'))
            <div class="success-box">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="error-box">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach    
            </div>
        @endif

        <div class="customer-card">
            <h2>Lista de clientes</h2>

            <table id="customerTable" class="display">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Telefono</th>
                        <th>Email</th>
                        <th>Total Gastado</th>
                        <th>Compras</th>
                        <th>Última Compra</th>
                        <th>Etiquetas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer->customer_id }}</td>
                            <td>{{ $customer->name_customer }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ $customer->email }}</td>                       
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No hay clientes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="customerModal">
        <div class="modal-box">
            <h2>Nuevo cliente</h2>
            <button type="button" class="modal-close" onclick="closeUserModal()">X</button>

            <div class="modal-body">
                <form method="POST" action="{{ route('customers.store') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label>Nombre del cliente</label>
                        <input type="text" name="name_customer" class="form-input" value="{{ old('name_customer') }}" required>
                    </div>
                
                    <div class="form-row">
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="phone" class="form-input" value="{{ old('phone') }}" required>
                        </div>
                    
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
                        </div>
                    </div>
                
                    <div class="form-group">
                        <label>Etiquetas</label>
                        <div class="tags-grid">
                            @foreach($tags as $tag)
                            <label class="tag-option">
                                <input type="checkbox" name="tags[]" value="{{ $tag->tag_id }}">
                                <span>{{ $tag->name_tag }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="token-button">
                        <h4>¿Tienes un codigo de cliente?</h4>
                        <input type="text" name="token_customer" class="form-input">
                    </div>
                                
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onClick="closeCustomerModal()">Cancelar</button>
                        <button type="submit" class="btn-primary">Guardar cliente</button>                
                    </div>
                </form>
            </div>
        </div>
    </div>
    

    <script>
        function openCustomerModal(){
            document.getElementById('customerModal').style.display = 'flex';
        }
        function closeCustomerModal(){
            document.getElementById('customerModal').style.display = 'none';
        }

        @if($errors->any())
            document.addEventListener('DOMContentLoaded', function(){
                openCustomerModal();
            })
        @endif
    </script>
@endsection
  