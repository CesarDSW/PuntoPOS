@extends('layout.dashboard_design')

@section('content')
    <div class="customer-page">
        <div class="customer-header">
            <div>
                <h1>Clientes</h1>
                <p>Administra la base de clientes para tu negocio.</p>
            </div>    

            <button type="button" class="btn-primary" onclick="openCustomerModal()"> 
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
                        <th>ID Cliente</th>
                        <th>Nombre</th>
                        <th>Telefono</th>
                        <th>Email</th>
                        <th>Total Gastado</th>
                        <th>Compras</th>
                        <th>Última Compra</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer->customer_code ?? ('CL-' . str_pad($customer->customer_id, 3, '0', STR_PAD_LEFT)) }}</td>
                            <td>{{ $customer->name_customer }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>
                                <div class="action-dropdown">
                                    <button type="button" class="action-btn" onclick="toggleActions({{ $customer->customer_id }})">
                                        Acciones
                                    </button>

                                    <div class="action-menu" id="actions-{{ $customer->customer_id }}">
                                        <a href="{{ route('customers.edit', $customer->customer_id) }}">Editar cliente</a>
                                        <a href="{{ route('customers.history', $customer->customer_id) }}">Ver historial</a>

                                        <form method="POST" action="{{ route('customers.delete', $customer->customer_id) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este cliente?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="delete-action">Eliminar cliente</button>
                                        </form>
                                    </div>
                                </div>
                            </td>                   
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No hay clientes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="customerModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Nuevo cliente</h2>
                <button type="button" class="modal-close" onclick="closeUserModal()">X</button>
            </div>
    
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
                
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closeCustomerModal()">Cancelar</button>
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

        function toggleActions(customerId){
            const currentId = 'actions-' + (customerId);
            const menu = document.getElementById(currentId);
            
            document.querySelectorAll('.action-menu').forEach(item => {
                if(item.id !== currentId){
                    item.style.display = 'none';
                }
            });

            menu.style.display = menu.style.display === 'block' ? 'none' : 'block'
        }

        document.addEventListener('click', function(event){
            if(!event.target.closest('.action-dropdown')){
                document.querySelectorAll('.action-menu').forEach(item =>{
                    item.style.display = 'none';
                });
            }
        });

        @if($errors->any())
            document.addEventListener('DOMContentLoaded', function(){
                openCustomerModal();
            })
        @endif
    </script>
@endsection
  