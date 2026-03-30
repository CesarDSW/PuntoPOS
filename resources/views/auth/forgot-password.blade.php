@extends('layout.auth_design')

@section('content')
    <h1 class="auth-title">Recuperar contraseña</h1>
    <p class="auth-subtittle">Te enviaremos un enlace a tu correo electrónico</p>

    @if(session('status'))
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

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
        </div>

        <button type="submit" class="auth-button">Enviar enlace</button>
    </form>
    
    <div>
        <a href="{{ route('login') }}" class="switch-link">Volver a iniciar sesion</a>
    </div>
@endsection