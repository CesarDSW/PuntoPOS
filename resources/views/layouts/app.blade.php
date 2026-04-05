<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Punto')</title>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background: #f6f7fb; color: #0f172a; }

        .app-shell { display: flex; min-height: 100vh; }

        .sidebar {
            width: 240px;
            background: #0b1736;
            color: white;
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo {
            padding: 24px 20px;
            font-size: 28px;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .sidebar-menu {
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-link {
            display: block;
            padding: 14px 16px;
            border-radius: 10px;
            color: white;
            text-decoration: none;
            background: transparent;
        }

        .sidebar-link:hover { background: rgba(255,255,255,.08); }
        .sidebar-link.active { background: #1d4ed8; }

        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .topbar {
            height: 80px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }

        .topbar-left input {
            width: 420px;
            max-width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .branch-selector { position: relative; }

        .branch-button {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 10px 14px;
            min-width: 220px;
            text-align: left;
            cursor: pointer;
        }

        .branch-label {
            display: block;
            font-size: 12px;
            color: #64748b;
        }

        .branch-name {
            display: block;
            font-weight: bold;
            margin-top: 4px;
        }

        .branch-dropdown {
            position: absolute;
            top: 110%;
            right: 0;
            width: 260px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0,0,0,.12);
            padding: 8px;
            display: none;
            z-index: 100;
        }

        .branch-dropdown.show { display: block; }

        .branch-option {
            display: block;
            width: 100%;
            text-align: left;
            background: transparent;
            border: none;
            padding: 12px 14px;
            border-radius: 10px;
            cursor: pointer;
        }

        .branch-option:hover { background: #ffeef8; }
        .branch-option.active { background: #1d4ed8; color: white; }

        .user-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: #1e40af;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .page-content { padding: 24px; }

        .page-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 24px;
        }

        .text-muted { color: #64748b; }

        .btn {
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            background: white;
            cursor: pointer;
        }

        @media (max-width: 900px) {
            .sidebar { width: 210px; }
            .topbar-left input { width: 240px; }
        }
    </style>
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