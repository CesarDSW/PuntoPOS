@extends('layout.auth_design')

@section('content')
<div class="auth-split">
    <div class="auth-left">
        <h1>Bienvenido</h1>
        <h2>Administra tu negocio fácilmente</h2>
        <p>Plataforma diseñada para gestionar ventas, inventario y clientes desde un solo lugar.</p>

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

    <div class="auth-right auth-right-base">
        <div class="login-card login-card-background">
            <h2 class="login-title">Iniciar sesión</h2>
            <p class="login-subtitle">Ingresa tus datos para acceder</p>

            <div class="input-group">
                <input type="email" placeholder="correo@ejemplo.com" disabled>
            </div>

            <div class="input-group">
                <input type="password" placeholder="********" disabled>
            </div>

            <div class="login-row">
                <label class="remember-box">
                    <input type="checkbox" disabled>
                    <span>Recordarme</span>
                </label>

                <span class="login-link">¿Olvidaste tu contraseña?</span>
            </div>

            <button type="button" class="btn-primary-auth" disabled>
                Iniciar sesión
            </button>

            <div class="divider"><span>o</span></div>

            <button type="button" class="google-button" disabled>
                <span class="google-icon">G</span>
                Continuar con Google
            </button>

            <div class="register-text">
                ¿No tienes cuenta?
                <span>Registrarse</span>
            </div>
        </div>

        <div class="auth-overlay">
            <div class="auth-modal-card">
                <div class="modal-top">
                    <div>
                        <h2 class="login-title modal-title-left">Recuperar contraseña</h2>
                        <p class="login-subtitle modal-subtitle-left">Te enviaremos un enlace de recuperación</p>
                    </div>

                    <a href="{{ route('login') }}" class="modal-close-btn">×</a>
                </div>

                @if (session('status'))
                    <div class="success-box">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="error-box">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="info-box-auth">
                    Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                </div>

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="input-group">
                        <label class="input-label">Correo electrónico</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="correo@ejemplo.com"
                            required
                        >
                    </div>

                    <div class="note-box-auth">
                        <strong>Nota:</strong> El enlace de recuperación expirará en 1 hora por seguridad.
                    </div>

                    <div class="modal-actions">
                        <a href="{{ route('login') }}" class="btn-secondary-auth">Cancelar</a>
                        <button type="submit" class="btn-primary-auth">Enviar enlace</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection