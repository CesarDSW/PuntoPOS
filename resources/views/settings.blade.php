@extends('layout.dashboard_design')

@section('content')
    <div class="settings-page">
        <div class="settings-header">
            <h1>Configuración</h1>
            <p>Admninistra las preferencias de tu negocio.</p>
        </div>
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

    <div class="settings-layout">
        <aside class="settings-menu">
            <a href="{{ route('settings', ['tab' => 'perfil']) }}"
            class="settings-menu-item {{ request('tab', 'perfil') == 'perfil' ? 'active' : '' }}">
            Perfil del negocio
            </a>

            <a href="{{ route('settings', ['tab' => 'usuarios']) }}"
            class="settings-menu-item {{ request('tab', 'usuarios') == 'usuarios' ? 'active' : '' }}">
            Usuarios y roles
            </a>

            <a href="{{ route('settings', ['tab' => 'pagos']) }}"
            class="settings-menu-item {{ request('tab', 'pagos') == 'pagos' ? 'active' : '' }}">
            Métodos de pago
            </a>

            <a href="{{ route('settings', ['tab' => 'notificaciones']) }}"
            class="settings-menu-item {{ request('tab', 'notificaciones') == 'notificaciones' ? 'active' : '' }}">
            Notificaciones
            </a>

            <a href="{{ route('settings', ['tab' => 'seguridad']) }}"
            class="settings-menu-item {{ request('tab', 'seguridad') == 'seguridad' ? 'active' : '' }}">
            Seguridad
            </a>

            <a href="{{ route('settings', ['tab' => 'preferencias']) }}"
            class="settings-menu-item {{ request('tab', 'preferencias') == 'preferencias' ? 'active' : '' }}">
            Preferencias
            </a>
        </aside>

        <section class="settings-content">
            @if(request('tab', 'perfil') == 'perfil')
                <div class="settings-card">
                    <h2>Información del negocio</h2>
                    
                    <form method='POST' action="{{ route('settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="tab_section" value="perfil">
                        
                        <div class="form-group">
                            <label>Logo</label>
                            <input type="file" name="logo" class="form-input">
                        </div>
                        
                        @if(!empty($company->logo))
                        <div class="form-group">
                            <label>Logo actual</label><br>
                            <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo de la empresa"
                            style="max-width: 180px; border-radius: 12px; border: 1px solid #d1d5db; padding: 8px;">
                        </div>
                        @endif
                        
                        <div class="form-group">
                            <label>Nombre de la empresa</label>
                            <input type="text" name="name_company" class="form-input" 
                            value="{{ old('name_company', $company->name_company) }}">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>RFC</label>
                                <input type="text" name="rfc" class="form-input" 
                                value="{{ old('rfc', $company->rfc) }}">
                            </div>
                            
                            <div class="form-group">
                                <label>Correo</label>
                                <input type="email" name="email" class="form-input"
                                value="{{ old('email', $company->email) }}">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Direccion</label>
                            <input type="text" name="address" class="form-input" 
                            value="{{ old('address', $company->address) }}">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Ciudad</label>
                                <input type="text" name="city" class="form-input" 
                                value="{{ old('city', $company->city) }}">
                            </div>
                        
                            <div class="form-group">
                                <label>Estado</label>
                                <input type="text" name="state" class="form-input" 
                                value="{{ old('state', $company->state) }}">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Codigo postal</label>
                                <input type="text" name="zip_code" class="form-input" 
                                value="{{ old('zip_code', $company->zip_code) }}">
                            </div>

                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="phone" class="form-input" 
                                value="{{ old('phone', $company->phone) }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Hora de apertura</label>
                                <input type="time" name="opening_time" class="form-input" 
                                value="{{ old('opening_time', $company->opening_time) }}">
                            </div>
                            
                            <div class="form-group">
                                <label>Hora de cierre</label>
                                <input type="time" name="closing_time" class="form-input" 
                                value="{{ old('closing_time', $company->closing_time) }}">
                            </div>   
                        </div>
                        
                        <div class="form-group">
                            <label>Tipo de moneda</label>
                            <select name="currency" class="form-input">
                                <option value="">Selecciona</option>
                                <option value="MXN" @selected(old('currency', $company->currency) == 'MXN')>MXN - Peso Mexicano</option>
                                <option value="USD" @selected(old('currency', $company->currency) == 'USD')>USD - Dólar</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Descripción del negocio</label>
                            <textarea name="description_company" class="form-textarea">{{ old('description_company', $company->description_company) }}</textarea>
                        </div>

                        <button type="submit" class="btn-save">Guardar cambios</button>
                    </form>
                </div>

            @elseif(request('tab') == 'usuarios')
                <div class="settings-card">
                    <div class="users-header">
                        <div>
                            <h2>Usuarios y roles</h2>
                            <p>Gestiona el acceso al sistema</p>
                        </div>
                        
                        <button type="button" class="btn-save" onClick="openUserModal()">
                           + Nuevo usuario
                        </button>
                    </div>
                    
                    <div class="roles-info-grid">
                        <div class="role-info-card role-admin">
                            <h3>Administrador</h3>
                            <p>Control total del sistema, configuración y acceso completo a todas las funciones</p>
                        </div>
                                
                        <div class="role-info-card role-manager">
                            <h3>Gerente</h3>
                            <p>Gestion de inventario, usuarios, clientes y reportes operativos.</p>
                        </div>
                                
                        <div class="role-info-card role-cashier">
                            <h3>Cajero</h3>
                            <p>Realiza ventas, consulta productos y atiende clientes en el POS.</p>
                        </div>
                    </div>    
                        
                    <div class="users-list">
                        @forelse($users as $userItem)
                        <div class="user-card">
                            <div>
                                <h3>{{ $userItem->name_user }}</h3>
                                <p>{{ $userItem->email }}</p>
                            </div>

                            <div>
                                @php
                                    $roleName = match($userItem->rol_idfk){
                                        1 => 'Administrador',
                                        2 => 'Gerente',
                                        3 => 'Cajero',
                                        default => 'Sin rol'
                                    };
                                @endphp

                                <span class="role-badge">{{ $roleName }}</span>
                            </div>
                        </div>
                        @empty
                        <p>No hay usuarios registrados.</p>
                        @endforelse
                    </div>
                </div>
                
                <div class="modal-overlay" id="userModal">
                    <div class="modal-box">
                        <div class="modal-header">
                            <h2>Nuevo usuario</h2>
                            <button type="button" class="modal-close" onclick="closeUserModal()">X</button>
                        </div>
                        
                        <form method="POST" action="{{ route('users.store') }}">
                            @csrf
                            
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Nombre del usuario</label>
                                    <input type="text" name="name_user" class="form-input" value="{{ old('name_user') }}" required>
                                </div>
                                    
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
                                    </div>
                                        
                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <input type="text" name="phone" class="form-input" value="{{ old('phone') }}" required>
                                    </div>
                                </div>
                                    
                                <div class="form-group">
                                    <label>Rol</label>
                                    <select name="rol_idfk" class="form-input" required>
                                        <option value="">Selecciona un rol</option>
                                        @foreach($roles as $rol)
                                            <option value="{{ $rol->rol_id }}" @selected(old('rol_idfk') == $rol->rol_id)>
                                                {{ $rol->type_rol }}
                                            </option>
                                        @endforeach
                                        </select>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Contraseña</label>
                                        <input type="password" name="password" class="form-input" value="{{ old('password') }}" required>
                                    </div>
                                        
                                    <div class="form-group">
                                        <label>Confirmar contraseña</label>
                                        <input type="password" name="password_confirmation" class="form-input" required>
                                    </div>
                                </div>
                            </div>
                                
                            <div class="modal-footer">
                                <button type="button" class="btn-secondary" onClick="closeUserModal()">Cancelar</button>
                                <button type="submit" class="btn-save">Guardar usuario</button>
                            </div>          
                        </form>
                    </div>
                </div>                   
                
                <script>
                function openUserModal(){
                    document.getElementById('userModal').style.display = 'flex';
                }

                function closeUserModal(){
                    document.getElementById('userModal').style.display = 'none';
                }

                @if($errors->any() && request('tab') == 'usuarios')
                    document.addEventListener('DOMContentLoaded', function(){
                        openUserModal();
                    });
                @endif
                </script>

            @elseif(request('tab') == 'pagos')
                <div class="settings-card">
                    <h2>Métodos de pago</h2>

                    @php
                        $paymentMethods = $company->payment_methods
                            ? json_decode($company->payment_methods, true)
                            : [];
                    @endphp
                    
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        <input type="hidden" name="tab_section" value="pagos">

                         <div class="form-group">
                            <label>Selecciona un método de pago</label>
                   
                            <div class="payment-grid">
                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Efectivo" {{ in_array('Efectivo', $paymentMethods) ? 'checked' : '' }}>
                                    <span>Efectivo</span>
                                </label>

                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Tarjeta" {{ in_array('Tarjeta', $paymentMethods) ? 'checked' : '' }}>
                                    <span>Tarjeta</span>
                                </label>

                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Transferencia" {{ in_array('Transferencia', $paymentMethods) ? 'checked' : '' }}>
                                    <span>Transferencia</span>
                                </label>

                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Cheque" {{ in_array('Cheque', $paymentMethods) ? 'checked' : '' }}>
                                    <span>Cheque</span>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-save">Guardar cambios</button>
                    </form>
                </div>
    
            @elseif(request('tab') == 'notificaciones')
                <div class="settings-card">
                    <h2>Notificaciones</h2>
                    <p>Configura las notificaciones que deseas recibir sobre tu negocio.</p>
                </div>
    
            @elseif(request('tab') == 'seguridad')
                <div class="settings-card">
                    <div class="user-header">
                       <h2>Cambiar contraseña</h2>
                        <p>Actualiza tu contraseña periodicamente</p>
                    </div>
                    
                    @if(session('success_password'))
                        <div class="success-box">
                            {{ session('success_password') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="error-box">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                        <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <div class="form-group">
                            <label>Contraseña actual</label>
                            <input type="password" name="current_password" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label>Nueva actual</label>
                            <input type="password" name="new_password" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label>Confirmar contraseña</label>
                            <input type="password" name="new_password_confirmation" class="form-input" required>
                        </div>
                        
                        <button type="submit" class="btn-save">Actualizar contraseña</button>
                    </form>
                    
                    <div class="form-group">
                        <p>Requisitos: La contraseña debe tener al menos 8 caracteres,
                            incluir mayúsculas, minúsculas y números.</p>
                    </div>
               </div>

            @elseif(request('tab') == 'preferencias')
                <div class="settings-card">
                    <div class="">

                    </div>
                    <h2>Preferencias</h2>
                    <p>Configura tus preferencias personales para una mejor experiencia.</p>
                </div>
            @endif
        </section>
    </div>  
@endsection