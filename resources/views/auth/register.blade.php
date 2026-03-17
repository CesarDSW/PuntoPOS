@extends('layout.auth_design')

@section('content')
    <h1 class="auth-title">Crear cuenta</h1>
    <p class="auth-subtitle">Registra tu usuario y tu negocio</p>
   
   @if($errors->any())
        <div class="error-box">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="form-group">
        <label class="form-label">Nombre</label>
        <input type="text" name="name_user" class="form-input" value="{{ old('name_user') }}" required>
    </div>

    <div class="form-group">
        <label class="form-label">Teléfono</label>
        <input type="text" name="phone" class="form-input" value="{{ old('phone') }}" required>
    </div>
    
     <div class="form-group">
        <label class="form-label">Correo electronico</label>
        <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
    </div>
    
     <div class="form-group">
        <label class="form-label">Nombre de la compañia</label>
        <input type="text" name="name_company" class="form-input" value="{{ old('name_company') }}" required>
    </div>
    
     <div class="form-group">
        <label class="form-label">Contraseña</label>
        <input type="password" name="password" class="form-input" required>
    </div>

     <div class="form-group">
        <label class="form-label">Confirmar contraseña</label>
        <input type="password" name="password_confirmation" class="form-input" required>
    </div>
        
    <button type="submit" class="auth-button">Registrarse</button>
</form>

<div class="switch-text">
    ¿Ya tienes cuenta?<br>
    <a href="{{ route('login') }}" class="switch-link">Volver</a>
</div>
@endsection