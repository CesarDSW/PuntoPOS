<aside class="sidebar">
    <div class="sidebar-logo">PUNTO</div>

    <nav class="sidebar-menu">
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('sales.index') }}" class="sidebar-link {{ request()->is('ventas*') ? 'active' : '' }}">Ventas</a>
        <a href="{{ route('catalog.index') }}" class="sidebar-link {{ request()->is('catalogo*') ? 'active' : '' }}">Catálogo</a>
        <a href="{{ route('inventory.index') }}" class="sidebar-link {{ request()->is('inventario*') ? 'active' : '' }}">Inventario</a>
        <a href="{{ route('payments.index') }}" class="sidebar-link {{ request()->is('pagos*') ? 'active' : '' }}">Pagos</a>
        <a href="{{ route('customers') }}" class="sidebar-link {{ request()->is('cliente*') ? 'active' : '' }}">Clientes</a>
        <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->is('reportes*') ? 'active' : '' }}">Reportes</a>
        <a href="{{ route('settings') }}" class="sidebar-link {{ request()->is('configuracion*') ? 'active' : '' }}">Configuración</a>
    </nav>
</aside>