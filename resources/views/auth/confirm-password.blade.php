@extends('layout.auth_design')

@section('content')
    <div class="auth-modal-page">
        <div class="auth-modal-card">
            <div class="modal-top">
                <div>
                    <h1 class="login-title modal-title-left">Confirmar contraseña</h1>
                    <p class="login-subtittle modal-subtitle-left">
                        Por seguridad, confirma tu contraseña para continuar
                    </p>
                </div>

                <a href="{{ url() ->previous()}}" class="modal-close-btn" aria-label="Cerrar">
                    &times;
                </a>
            </div>

            <div class="info-box-auth">
                Esta validación protege cambios sensibles, como la activación de la verificación en dos pasos. 
            </div>

            @if($errors->any())
                <div class="error-box">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="/user/confirm-password">
                @csrf

                <div class="input-group">
                    <label for="password" class="input-label">Contraseña</label>
                    <input 
                        id="password"
                        type="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Escribe tu contraseña"
                        autocomplete="current-password"
                        required
                        autofocus
                    >
                </div>

                <div class="note-box-auth">
                    Debes ingresar tu contraseña actual para continuar con el proceso de seguridad. 
                </div>

                <div class="modal-actions">
                    <a href="{{ url() ->previous()}}" class="btn-secondary-auth">
                        Cancelar
                    </a>

                    <button type="submit" class="btn-primary-auth">
                        Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection