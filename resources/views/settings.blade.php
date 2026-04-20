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
             🏢Perfil del negocio
            </a>

            <a href="{{ route('settings', ['tab' => 'usuarios']) }}"
            class="settings-menu-item {{ request('tab', 'usuarios') == 'usuarios' ? 'active' : '' }}">
            👥Usuarios y roles
            </a>

            <a href="{{ route('settings', ['tab' => 'pagos']) }}"
            class="settings-menu-item {{ request('tab', 'pagos') == 'pagos' ? 'active' : '' }}">
            💳Métodos de pago
            </a>

            <a href="{{ route('settings', ['tab' => 'notificaciones']) }}"
            class="settings-menu-item {{ request('tab', 'notificaciones') == 'notificaciones' ? 'active' : '' }}">
            🔔Notificaciones
            </a>

            <a href="{{ route('settings', ['tab' => 'seguridad']) }}"
            class="settings-menu-item {{ request('tab', 'seguridad') == 'seguridad' ? 'active' : '' }}">
            🛡️Seguridad
            </a>

            <a href="{{ route('settings', ['tab' => 'preferencias']) }}"
            class="settings-menu-item {{ request('tab', 'preferencias') == 'preferencias' ? 'active' : '' }}">
            ⚙️Preferencias
            </a>
        </aside>

        <section class="settings-content">
            @if(request('tab', 'perfil') == 'perfil')
                <div class="settings-card">
                    <h2>🏢Información del negocio</h2>
                    
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
                            <img src="{{ asset('storage/' . $company->logo) }}" 
                            alt="Logo de la empresa"
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
                            <h2>👥Usuarios y roles</h2>
                            <p>Gestiona el acceso al sistema</p>
                        </div>
                        
                        <button type="button" class="btn-new-user" onclick="openUserModal()">
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

                            <div class="user-actions-right">
                                @php
                                    $roleName = match($userItem->rol_idfk){
                                        1 => 'Administrador',
                                        2 => 'Gerente',
                                        3 => 'Cajero',
                                        default => 'Sin rol'
                                    };
                                @endphp

                                <span class="role-badge">{{ $roleName }}</span>

                                <div class ="user-action-buttons">
                                    <button type="button" class="btn-icon-edit"
                                        onclick="openEditUserModal(
                                            '{{ $userItem->userr_id }}',
                                            '{{ $userItem->name_user }}',
                                            '{{ $userItem->phone }}',
                                            '{{ $userItem->email }}',
                                            '{{ $userItem->rol_idfk }}'
                                        )">
                                            Editar
                                    </button>

                                    <form method="POST" action="{{ route('users.delete', $userItem->userr_id) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon-delete">Eliminar</button>
                                    </form>
                                </div>
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
                                        <input type="password" name="password" class="form-input" required>
                                    </div>
                                        
                                    <div class="form-group">
                                        <label>Confirmar contraseña</label>
                                        <input type="password" name="password_confirmation" class="form-input" required>
                                    </div>
                                </div>
                            </div>
                                
                            <div class="modal-footer">
                                <button type="button" class="btn-secondary" onclick="closeUserModal()">Cancelar</button>
                                <button type="submit" class="btn-save">Guardar usuario</button>
                            </div>          
                        </form>
                    </div>
                </div>
                
                <div class="modal-overlay" id="editUserModal">
                    <div class="modal-box">
                        <div class="modal-header">
                            <h2>Editar usuario</h2>
                            <button type="button" class="modal-close" onclick="closeEditUserModal()">X</button>
                        </div>

                        <form method="POST" id="editUserForm">
                            @csrf
                            @method('PUT')

                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Nombre del usuario</label>
                                    <input type="text" name="name_user" id="edit_name_user" class="form-input" required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" id="edit_email"  class="form-input" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <input type="text" name="phone" id="edit_phone" class="form-input" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Rol</label>
                                    <select name="rol_idfk" id="edit_rol_idfk" class="form-input">
                                        <option value="">Selecciona un rol</option>
                                        @foreach($roles as $rol)
                                            <option value="{{ $rol->rol_id }}">{{ $rol->type_rol }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="future-box">
                                    asignar usuario a una sucursal
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn-secondary" onclick="closeEditUserModal()">Cancelar</button>
                                <button type="submit" class="btn-save">Guardar cambios</button>
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

                function openEditUserModal(id, name, phone, email, rolId){
                    document.getElementById('edit_name_user').value = name;
                    document.getElementById('edit_phone').value = phone;
                    document.getElementById('edit_email').value = email;
                    document.getElementById('editUserForm').action = '/configuracion/usuarios/' + id;
                    document.getElementById('editUserModal').style.display = 'flex';
                }

                function closeEditUserModal(){
                    document.getElementById('editUserModal').style.display = 'none';
                }

                @if($errors->any() && request('tab') == 'usuarios')
                    document.addEventListener('DOMContentLoaded', function(){
                        openUserModal();
                    });
                @endif
                </script>

            @elseif(request('tab') == 'pagos')
                <div class="settings-card">
                    <h2>💳Métodos de pago</h2>

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
                                  <div class="payment-content">
                                      💵 <span>Efectivo</span>
                            </div>
                                </label>

                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Tarjeta" {{ in_array('Tarjeta', $paymentMethods) ? 'checked' : '' }}>
                                    <span>💳Tarjeta</span>
                                </label>

                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Transferencia" {{ in_array('Transferencia', $paymentMethods) ? 'checked' : '' }}>
                                    <span>🔁Transferencia</span>
                                </label>

                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Cheque" {{ in_array('Cheque', $paymentMethods) ? 'checked' : '' }}>
                                    <span>🧾Cheque</span>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-save">Guardar cambios</button>
                    </form>
                </div>
    
            @elseif(request('tab') == 'notificaciones')
                <div class="settings-card">
                    <h2>🔔Notificaciones</h2>
                    <p>Configura las notificaciones que deseas recibir sobre tu negocio.</p>

                    <form method="POST" action="{{ route('settings.notifications.update') }}">
                        @csrf

                        <div class="settings-option-card">
                            <div>
                                <h3>Stock bajo</h3>
                                <p>Recibir alerta cuando un producto tenga poco inventario.</p>
                            </div>

                            <label class="switch">
                                <input type="checkbox" name="notify_low_stock" {{ $settings->notify_low_stock ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="settings-option-card">
                            <div>
                                <h3>Venta cancelada</h3>
                                <p>Recibir alerta cuando una venta sea cancelada.</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="notify_sale_cancelled" {{ $settings->notify_sale_cancelled ? 'checked':''}}>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="settings-option-card">
                            <div>
                                <h3>Producto agotado</h3>
                                <p>Recibir alerta cuando un producto llegue a 0 en stock.</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="notify_out_of_stock" {{ $settings->notify_out_of_stock ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <button type="submit" class="btn-save">Guardar notificaciónes</button>
                    </form>
                </div>
    
            @elseif(request('tab') == 'seguridad')
                <div class="settings-card">
                    <h2>🔐Seguridad</h2>
                    <p>Administra la seguridad de tu cuenta y protege el acceso a tu negocio.</p>

                    @if(session('success_password'))
                        <div class="success-box">
                            {{ session('success_password') }}
                        </div>
                    @endif

                    @if(session('status') == 'two-factor-authentication-enabled')
                        <div class="success-box">
                            Escanea el código QR y confirma el código de Google Authenticator.
                        </div>
                    @endif

                    @if(session('status') == 'two-factor-authentication-confirmed')
                        <div class="success-box">
                            La autenticación en dos pasos fue confirmada correctamente.
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="error-box">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                    
                    <div class="security-block">
                        <div class="security-block-header">
                            <h3>Cambiar contraseña</h3>
                            <p>Actualiza tu contraseña periódicamente</p>
                        </div
                        >
                        <form method="POST" action="{{ route('settings.password.update') }}">
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
                        
                            <div class="security-info-box">
                                <strong>Requisitos:</strong> La contraseña debe tener al menos 8 caracteres,
                                incluir mayúsculas, minúsculas y números.
                            </div>

                            <button type="submit" class="btn-save">Cambiar contraseña</button>
                        </form>
                    </div>
                     
                    <div class="security-block">
                        <div class="security-block-header twofa-header">
                            <div class="twofa-header-icon">📱</div>
                            <div>
                                <h3>Autenticación de dos factores (2FA)</h3> 
                                <p>Agrega una capa extra de seguridad a tu cuenta</p>
                            </div>
                        </div>
                        
                        @if(empty(auth()->user()->two_factor_secret))
                            <div class="twofa-status-box twofa-status-off">
                                <div>
                                    <strong>2FA desactivado</strong>
                                    <p>Activa la autenticación de dos factores para mayor seguridad.</p>
                                </div>
                                
                                <form method="POST" action="/user/two-factor-authentication">
                                    @csrf
                                    
                                    <button type="submit" class="btn-save">Activar verificación en dos pasos</button>
                                </form>
                            </div>
                        @else
                            <div class="twofa-status-box twofa-status-on">
                                <div>
                                    <strong>2FA en proceso o activado</strong>
                                    <p>Escanea el código QR con Google Authenticator y confirma el código.</p>
                                </div>
                            </div>

                            <div class="twofa-qr-box">
                                {!! auth()->user()->twoFactorQrCodeSvg() !!}
                            </div>

                            @if(is_null(auth()->user()->two_factor_confirmed_at))
                                <form method="POST" action="/user/confirmed-two-factor-authentication" class="twofa-confirm-form">
                                    @csrf

                                    <div class="form-group">
                                        <label>Código de verificación</label>
                                        <input type="text" name="code" class="form-input" maxlength="6" placeholder="123456" required>
                                    </div>

                                <button type="submit" class="btn-save">Confirmar verificación</button>
                            </form>
                            
                            @else
                                <div class="twofa-confirmed-badge">
                                    Verificación en dos pasos activada correctamente.
                                </div>
                            @endif

                            <div class="recovery-section">
                                <h4>Códigos de recuperación</h4>
                                <p>Guárdalos en un lugar seguro. Te servirán si pierdes acceso a tu teléfono.</p>
                                
                                <div class="recovery-codes-box">
                                    @foreach(auth()->user()->recoveryCodes() as $code)
                                        <div>{{ $code }}</div>
                                    @endforeach
                                </div>

                                <form method="POST" action="/user/two-factor-recovery-codes" style="margin-top: 14px;">
                                    @csrf
                                    <button type="submit" class="btn-secondary">Regenerar códigos</button>
                                </form>
                            </div>

                            <form method="POST" action="/user/two-factor-authentication" style="margin-top: 18px;">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn-danger">Desactivar verificación en dos pasos</button>
                            </form>
                        @endif
                    </div>
                </div>
                    
            @elseif(request('tab') == 'preferencias')
                <div class="settings-card">
                    <h2>⚙️Preferencias</h2>
                    <p>Configura tus preferencias generales del sistema.</p>

                    <form method="POST" action="{{ route('settings.preferences.update') }}">
                        @csrf

                        <div class="settings-card inner-card">
                            <h3>Configuración regional</h3>
                            <p>Idioma, zona horaria y formatos</p>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Idioma</label>
                                    <select name="language" class="form-input">
                                        <option value="Español (México)" @selected($settings->language == 'Español (México)')>Español (México)</option>
                                        <option value="English (US)" @selected($settings->language == 'English (US)')>English (US)</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Zona horaria</label>
                                    <select name="timezone" class="form-input">
                                        <option value="Ciudad de México (GMT-6)" @selected($settings->timezone == 'Ciudad de México (GMT-6)')>Ciudad de México (GMT-6)</option>
                                        <option value="Monterrey (GMT-6)" @selected($settings->timezone == 'Monterrey (GMT-6)')>Monterrey (GMT-6)</option>                
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Formato de fecha</label>
                                    <select name="date_format" class="form-input">
                                        <option value="DD/MM/YYYY" @selected($settings->date_format == 'DD/MM/YYYY')>DD/MM/YYYY (31/12/2024)</option>
                                        <option value="MM/DD/YYYY" @selected($settings->date_format == 'MM/DD/YYYY')>MM/DD/YYYY (12/31/2024)</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Formato de hora</label>
                                    <select name="time_format" class="form-input">
                                        <option value="24 horas" @selected($settings->time_format == '24 horas')>24 horas (18:30)</option>
                                        <option value="12 horas" @selected($settings->time_format == '12 horas')>12 horas (6:30 PM)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class ="settings-card inner-card">
                            <h3>Preferencias de impresión</h3>
                            <p>Configuración de tickets y recibos</p>

                            <div class="settings-option-card">
                                <div>
                                    <h3>Impresión automática</h3>
                                    <p>Imprimir ticket automáticamente al completar una venta</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="auto_print" {{ $settings->auto_print ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="settings-option-card">
                                <div>
                                    <h3>Mostrar impuestos</h3>
                                    <p>Desglosar impuestos en tickets y recibos</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="show_taxes" {{ $settings->show_taxes ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="form-group">
                                <label>Tamaño de impresora térmica</label>
                                <div class="printer-options">
                                    <label class="printer-option {{ $settings->printer_width == '80mm' ? 'selected' : '' }}">
                                        <input type="radio" name="printer_width" value="80mm" {{ $settings->printer_width == '80mm' ? 'checked' : ''}}>
                                        <span>80mm</span>
                                        <small>Impresora estándar</small>
                                    </label>

                                    <label class="printer-option {{ $settings->printer_width ==  '58mm' ? 'selected' : '' }}">
                                        <input type="radio" name="printer_width" value="58mm" {{ $settings->printer_width == '58mm' ? 'checked' : ''}}>
                                        <span>58mm</span>
                                        <small>Impresora compacta</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        

                        <div class="settings-card inner-card">
                            <h3>Preferencias de visualización</h3>
                            <p>Apariencia y formato de números</p>

                            <div class="form-group">
                                <label>Tema de la interfaz</label>
                                <div class="theme-options">
                                    <label class="theme-card {{ $settings->theme == 'Claro' ? 'selected' : '' }}">
                                        <input type="radio" name="theme" value="Claro" {{ $settings->theme == 'Claro' ? 'checked' : '' }}>
                                        <div class="theme-content">☀️ <span>Claro</span></div>
                                    </label>

                                    <label class="theme-card {{ $settings->theme == 'Oscuro' ? 'selected' : '' }}">
                                        <input type="radio" name="theme" value="Oscuro" {{ $settings->theme == 'Oscuro' ? 'checked' : '' }}>
                                        <div class="theme-content">🌙 <span>Oscuro</span></div>
                                    </label>

                                    <label class="theme-card {{ $settings->theme == 'Auto' ? 'selected' : '' }}">
                                        <input type="radio" name="theme" value="Auto" {{ $settings->theme == 'Auto' ? 'checked' : '' }}>
                                        <div class="theme-content">⚙️ <span>Auto</span></div>
                                    </label>
                                </div>
                            </div>


                            
                            <div class="form-group">
                                <label>Decimales en precios</label>
                                <select name="price_decimals" class="form-input">
                                    <option value="2" @selected($settings->price_decimals == '2')>2 decimales ($100.00)</option>
                                    <option value="0" @selected($settings->price_decimals == '0')>0 decimales ($100)</option>
                                </select>
                            </div>
                        </div>

                        <div class="preferences-actions">
                            <button type="submit" class="btn-save">Guardar preferencias</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('settings.preferences.reset') }}">
                        @csrf

                        <br><button type="submit" class="btn-secondary">Restablecer valores por defecto</button>
                    </form>
                </div>
            @endif
        </section>
    </div>  
    
@endsection
