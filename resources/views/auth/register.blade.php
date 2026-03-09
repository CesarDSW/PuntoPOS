<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
</head>
<body>
    <h1>Registro</h1>

    <form method="POST" action="{{ route('register') }}">
    @csrf

    <input type="text" name="name_user" placeholder="Nombre" value="{{ old('name_user') }}" required>

    <input type="text" name="phone" placeholder="Telefono" value="{{ old('phone') }}" required>
    
    <input type="email" name="email" placeholder="Correo" value="{{ old('email') }}" required>
    
    <input type="text" name="name_company" placeholder="Nombre de la empresa">

    <input type="password" name="password" placeholder="Contraseña">

    <input type="password" name="password_confirmation" placeholder="Confirmar contraseña">

    <button type="submit">Registrarse</button>
</form>

@if ($errors->any())
<ul>
    @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
    @endforeach
</ul>
@endif
</body>
</html>