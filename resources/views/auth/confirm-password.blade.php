@extends('layout.auth_design')

@section('content')
    <h1 class="auth-title">Confirmar contraseña</h1>
    <p class="auth-subtittle">Por seguridad, confirma tu contraseña para continuar</p>

    @if($errors->any())
        <div class="error-box">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="/user/confirm-password">
        @csrf

        <div class="form-group">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-input" required>
        </div>

        <button type="submit" class="auth-button">Confirmar</button>
    </form>
@endsection