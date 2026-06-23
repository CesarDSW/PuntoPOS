@extends('layout.auth_design')

@section('content')
<div class="auth-container"> {{-- 🔥 CONTENEDOR PRINCIPAL --}}
    <div class="auth-split">
        <!-- IZQUIERDA -->
        <div class="auth-left">
            <h1>Bienvenido</h1>
            <h2>Administra tu negocio fácilmente</h2>
            <p>Plataforma diseñada para gestionar ventas, inventario y clientes</p>

            <div class="feature">
                <div class="icon">📊</div>
                <div class="feature-text">
                    <strong>Control de ventas</strong>
                    <span>Consulta reportes en tiempo real</span>
                </div>
            </div>

            <div class="feature">
                <div class="icon">⚙️</div>
                <div class="feature-text">
                    <strong>Gestión eficiente</strong>
                    <span>Administra productos fácilmente</span>
                </div>
            </div>

            <div class="feature">
                <div class="icon">🔒</div>
                <div class="feature-text">
                    <strong>Seguridad</strong>
                    <span>Protección avanzada de datos</span>
                </div>
            </div>
        </div>

        <!-- DERECHA -->
        <div class="auth-right">
            <div class="login-card">
                <h2 class="login-title">Iniciar sesión</h2>
                <p class="login-subtitle">Ingresa tus datos para acceder</p>
                
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
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <!-- EMAIL -->
                    <div class="input-group">
                        <input 
                            type="email" 
                            name="email" 
                            placeholder="correo@ejemplo.com"
                            value="{{ old('email') }}" 
                            required
                        >
                    </div>
                    
                    <!-- PASSWORD -->
                    <div class="input-group">
                        <input 
                            type="password" 
                            name="password" 
                            placeholder="*********" 
                            required
                        >
                    </div>

                    <!-- OPCIONES -->
                    <div class="login-row">
                        <label class="remember-box">
                            <input type="checkbox" name="remember">
                            <span>Recordarme</span>
                        </label>

                        <a href="{{ route('password.request') }}" class="login-link">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <!-- BOTON -->
                    <button type="submit" class="btn-primary-auth">
                        Iniciar sesión
                    </button>
                </form>

                <div class="divider">
                    <span>o</span>
                </div>

                <a href="{{ route('google.redirect') }}" class="google-button">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google">
                    <span>Continuar con Google</span>
                </a>

                <div class="register-text">
                    ¿No tienes cuenta?
                    <a href="{{ route('register') }}" class="switch-link">Registrarse</a>
                </div>   
            </div>
        </div>
    </div>
@endsection