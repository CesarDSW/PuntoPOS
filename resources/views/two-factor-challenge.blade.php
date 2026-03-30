@extends('layout.auth_design')

@section('content')
    <div class="auth-card">
        <h1>Verificación en dos pasos</h1>
        <p>Escribe tu código de Google Authenticator o un código de recuperación</p>

        @if($errors->any())
            <div class="error-box">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="/two-factor-challenge">
            @csrf

            <div class="form-group">
                <label>Código de autenticación</label>
                <input type="text" name="code" class="form-input" maxlenght="6">
            </div>

            <div class="form-group">
                <label>O código de recuperación</label>
                <input type="text" name="recovery_code" class="form-input">
            </div>

            <button type="submit" class="auth-button">
                Verificar
            </button>
        </form>
    </div>
@endsection