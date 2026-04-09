@extends('layout.auth_design')

@section('content')
    <div class="auth-split">
        <div class="auth-left">
            <h1>Punto POS</h1>
            <h2>Administra tu negocio de forma inteligente</h2>
            <p>Crea tu cuenta para comenzar a gestionar ventas, inventario y clientes</p>

            <div class="feature">
                <div class="icon">📊</div>
                <div class="feature-text">
                    <strong>Control total</strong>
                    <span>Ventas, inventario y clientes en un solo lugar</span>
                </div>
            </div>

            <div class="feature">
                <div class="icon">⚡</div>
                <div class="feature-text">
                    <strong>Rapido y eficiente</strong>
                    <span>Optimiza tus procesos diarios</span>
                </div>
            </div>

             <div class="feature">
                <div class="icon">🔒</div>
                <div class="feature-text">
                    <strong>Seguro</strong>
                    <span>Protección avanzada para tu información</span>
                </div>
            </div>
        </div>

        <div class="auth-right">
            <div class="login-card">
                <h2 class="login-title">Crear cuenta</h2>
                <p class="login-subtitle">Registra tu usuario y tu negocio</p>

                @if($errors->any())
                    <div class="error-box">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                @if(session('info'))
                    <div class="success-box">
                        {{ session('info') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    <div class="input-group">
                        <label class="input-label">Nombre Completo</label>
                        <input 
                            type="text" 
                            name="name_user" 
                            value="{{ old('name_user', $googleUser['name_user'] ?? '') }}" 
                            placegolder= "Tu nombre completo"
                            required
                            >
                    </div>
                    
                    <div class="input-group">
                        <label class="input-label">Teléfono</label>
                        <input 
                            type="text" 
                            name="phone" 
                            value="{{ old('phone') }}" 
                            placeholder= "8711234567"
                            required
                            >
                    </div>
                    
                    <div class="input-group">
                        <label class="input-label">Correo electrónico</label>
                        <input 
                            type="email" 
                            name="email"
                            value="{{ old('email', $googleUser['google_email'] ?? '') }}" {{ isset($googleUser['google_email']) ? 'readonly' : '' }} 
                            placeholder= "correo@ejemplo.com"
                            required
                            >
                    </div>
                    
                    <div class="input-group">
                        <label class="input-label">Nombre del negocio</label>
                        <input 
                            type="text" 
                            name="name_company"
                            value="{{ old('name_company') }}" 
                            placeholder= "Mi negocio"
                            required
                            >
                    </div>
                    
                    <div class="input-group">
                        <label class="input-label">Contraseña</label>
                        <input 
                            type="password" 
                            name="password"
                            placeholder= "*********"
                            required>
                    </div>
                    
                    <div class="input-group">
                        <label class="input-label">Confirmar contraseña</label>
                        <input 
                            type="password" 
                            name="password_confirmation"  
                            placeholder= "*********"
                            required
                            >
                    </div>
                    <button type="submit" class="btn-primary-auth">
                        Registrarse
                    </button>
                </form>
                
                <div class="register-text">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}">Inicia sesión</a>
                </div>
            </div>  
        </div>
    </div>       
@endsection