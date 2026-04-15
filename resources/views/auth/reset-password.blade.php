@extends('layout.auth_design')

@section('content')
<div class="auth-split">
    <div class="auth-left">
        <h1>Recupera tu acceso</h1>
        <h2>Estás a un paso de volver</h2>
        <p>Escribe tu nueva contraseña para restablecer el acceso a tu cuenta de Punto.</p>

        <div class="feature">
            <div class="icon">🔐</div>
            <div class="feature-text">
                <strong>Nueva contraseña</strong>
                <span>Actualiza tu acceso de forma segura</span>
            </div>
        </div>

        <div class="feature">
            <div class="icon">📩</div>
            <div class="feature-text">
                <strong>Proceso protegido</strong>
                <span>El enlace de recuperación es temporal y seguro</span>
            </div>
        </div>

        <div class="feature">
            <div class="icon">⚡</div>
            <div class="feature-text">
                <strong>Acceso rápido</strong>
                <span>Vuelve a entrar a tu cuenta en pocos pasos</span>
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

            <button type="button" class="btn-primary-auth" disabled>
                Iniciar sesión
            </button>
        </div>

        <div class="auth-overlay">
            <div class="auth-modal-card">
                <div class="modal-top">
                    <div>
                        <h2 class="login-title modal-title-left">Restablecer contraseña</h2>
                        <p class="login-subtitle modal-subtitle-left">Escribe tu nueva contraseña</p>
                    </div>

                    <a href="{{ route('login') }}" class="modal-close-btn">×</a>
                </div>

                @if($errors->any())
                    <div class="error-box">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ request()->email }}">

                    <div class="input-group">
                        <label class="input-label">Nueva contraseña</label>
                        <input
                            type="password"
                            name="password"
                            placeholder="********"
                            required
                        >
                    </div>

                    <div class="input-group">
                        <label class="input-label">Confirmar contraseña</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            placeholder="********"
                            required
                        >
                    </div>

                    <div class="note-box-auth">
                        <strong>Nota:</strong> Usa una contraseña segura con al menos 8 caracteres.
                    </div>

                    <div class="modal-actions">
                        <a href="{{ route('login') }}" class="btn-secondary-auth">Cancelar</a>
                        <button type="submit" class="btn-primary-auth">Guardar contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection