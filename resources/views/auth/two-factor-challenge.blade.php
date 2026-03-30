@extends('layout.auth_design')

@section('content')

    <h1 class="auth-title">Verificación en dos pasos</h1>
    <p>Escribe el código de tu app autenticadora o un código de recuperación</p>

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
            <label class="form-label">O código de recuperación</label>
            <input type="text" name="recovery_code" class="form-input">
        </div>

        <button type="submit" class="auth-button">Verificar</button>
    </form>
@endsection