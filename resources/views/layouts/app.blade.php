<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Punto')</title>
    
    <link rel="stylesheet" href="{{ asset('css/layout/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/topbar.css') }}">

    @stack('styles')
</head>

<body>
    <div class="app-shell">
        @include('partials.sidebar')

        <div class="main-wrapper">
            @include('partials.topbar')

            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>