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
                    <h2>Usuarios y roles</h2>
                    <p>Administra los usuarios que pueden acceder a tu sistema y sus permisos.</p>
                </div>

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
                    <h2>Seguridad</h2>
                    <p>Administra las opciones de seguridad para proteger tu cuenta y datos.</p>
                </div>

            @elseif(request('tab') == 'preferencias')
                <div class="settings-card">
                    <h2>Preferencias</h2>
                    <p>Configura tus preferencias personales para una mejor experiencia.</p>
                </div>
            @endif
        </section>
    </div>  
@endsection