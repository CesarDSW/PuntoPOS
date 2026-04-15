@extends('layout.auth_design')

@section('content')
    <div class="auth-modal-page">
        <div class="auth-modal-card">
            <h1 class="login-tittle">Verificación en dos pasos</h1>
            <p class="login-subtitle">Escribe tu código de Google Authenticator o un código de recuperación</p>
        
            @if($errors->any())
                <div class="error-box">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            
            <form method="POST" action="{{ url('/two-factor-challenge') }}">
                @csrf

                <div class="input-group">
                    <label class="input-label">Código de autenticación</label>
                    <input 
                        type="text" 
                        name="code" 
                        class="form-input" 
                        maxlength="6" 
                        placeholder="123456" 
                        autocomplete="one-time-code"
                    >
                </div>

                <div class="challenge-divider">
                    O
                </div>

                <div class="input-group">
                    <label class="input-label">Código de recuperación</label>
                    <input 
                        type="text" 
                        name="recovery_code" 
                        class="form-input"
                        placeholder="Pega aquí tu código de verificación"
                    >
                </div>

                <button type="submit" class="btn-primary-auth">
                    Verificar
                </button>
            </form>

            <div class="register-text">
                <a href="{{ route('login') }}">Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
@endsection