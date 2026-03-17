@extends('layout.auth_design')

@section('content')
  <h1 class="auth-title">Iniciar sesion</h1>
  <p class="auth-subtitle">Accede a tu cuenta para continuar</p>
  
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

        <div class="form-group">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-input" required>
        </div>

        <button type="submit" class="auth-button">Iniciar sesion</button>
    </form>
    
    <div class="switch-text">
        ¿No tienes cuenta?<br>
        <a href="{{ route('register') }}" class="switch-link">Registrarse</a>
    </div>
@endsection