@extends('layout.auth_design')

@section('content')
    <h1 class="auth-title">Nueva contraseña</h1>
    <p class="auth-subtitle">Escribe tu nueva contraseña</p>

    @if($errors->any())
        <div class="error-box">
            @foreach($errors-all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="email" class="form-input" value="{{ old('email', request()->email) }}" required>
        </div>

        
        <div class="form-group">
            <label class="form-label">Nueva contraseña</label>
            <input type="password" name="password" class="form-input" required>
        </div>

        
        <div class="form-group">
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" class="form-input" required>
        </div>

        <button type="submit" class="auth-button">Restablecer contraseña</button>
    </form>
@endsection