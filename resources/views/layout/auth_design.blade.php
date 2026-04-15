<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticacion</title>
    <link rel="stylesheet" href="{{ asset('css/layout/auth.css') }}">
    @stack('styles')
</head>
<body>
    <div class="auth-container">
        @yield('content')
    </div>
</body>
</html>