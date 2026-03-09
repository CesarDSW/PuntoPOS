<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Iniciar Sesion</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <input type="email" name="email" placeholder="Correo" value="{{ old('email') }}">

        <input type="password" name="password" placeholder="Contraseña">
        <br>

        <button type="submit">Iniciar sesion</button>
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