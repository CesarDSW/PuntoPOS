<button id="menuBtnsidebar" class="menu-toggle">☰</button>
<aside class="sidebar" id="sidebar">

    <img src="{{ asset('imagenes/logo.png') }}" alt="Logo">

    <nav class="sidebar-menu">
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">🏠 Dashboard</a>
        <a href="{{ route('sales.index') }}" class="sidebar-link {{ request()->is('ventas*') ? 'active' : '' }}">💰 Ventas</a>
        <a href="{{ route('catalog.index') }}" class="sidebar-link {{ request()->is('catalogo*') ? 'active' : '' }}">📦 Catálogo</a>
        <a href="{{ route('inventory.index') }}" class="sidebar-link {{ request()->is('inventario*') ? 'active' : '' }}">📊 Inventario</a>
        <a href="{{ route('payments.index') }}" class="sidebar-link {{ request()->is('pagos*') ? 'active' : '' }}">💳 Pagos</a>
        <a href="{{ route('customers') }}" class="sidebar-link {{ request()->is('cliente*') ? 'active' : '' }}">👥 Clientes</a>
        <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->is('reportes*') ? 'active' : '' }}">📈 Reportes</a>
        <a href="{{ route('settings') }}" class="sidebar-link {{ request()->is('configuracion*') ? 'active' : '' }}">⚙️ Configuración</a>
    </nav>

    <!-- AYUDA -->
    <div class="help-sidebar">
        <h4>¿Necesitas ayuda?</h4>
        <p>Centro de ayuda</p>
        <button type="button" onclick="abrirAyuda()">Abrir ayuda</button>
    </div>

    <!-- MODAL AYUDA -->
    <div id="modalAyuda" class="modal-ayuda">
        <div class="modal-contenido">

            <span class="cerrar" onclick="cerrarAyuda()">✖</span>

            <h2>Centro de Ayuda</h2>

            <input 
                type="text" 
                id="buscadorFAQ" 
                placeholder="Buscar ayuda..." 
                onkeyup="buscarFAQ()"
            >

            <div class="faq">

                <div class="item">
                    <h4 onclick="toggleFAQ(this)">📌 ¿Cómo registrar una venta?</h4>
                    <p>Ve al módulo de ventas, selecciona productos y confirma el pago.</p>
                </div>

                <div class="item">
                    <h4 onclick="toggleFAQ(this)">📌 ¿Cómo agregar productos?</h4>
                    <p>En la sección catálogo puedes registrar nuevos productos.</p>
                </div>

                <div class="item">
                    <h4 onclick="toggleFAQ(this)">📌 ¿Cómo ver reportes?</h4>
                    <p>Accede a la sección reportes para ver estadísticas.</p>
                </div>

            </div>

        </div>
    </div>

</aside>

<!-- OVERLAY -->
<div id="overlay" class="overlay"></div>

<!-- JS -->
<script>
// =====================
// SIDEBAR RESPONSIVE
// =====================
const btn = document.getElementById('menuBtnsidebar');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

btn.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
});

overlay.addEventListener('click', () => {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
});

// =====================
// MODAL AYUDA
// =====================
function abrirAyuda() {
    document.getElementById("modalAyuda").style.display = "flex";
}

function cerrarAyuda() {
    document.getElementById("modalAyuda").style.display = "none";
}

window.onclick = function(e) {
    let modal = document.getElementById("modalAyuda");
    if (e.target === modal) {
        cerrarAyuda();
    }
};

// =====================
// FAQ
// =====================
function toggleFAQ(element) {
    let p = element.nextElementSibling;
    p.style.display = (p.style.display === "block") ? "none" : "block";
}

function buscarFAQ() {
    let input = document.getElementById("buscadorFAQ").value.toLowerCase();
    let items = document.querySelectorAll(".faq .item");

    items.forEach(item => {
        let texto = item.innerText.toLowerCase();
        item.style.display = texto.includes(input) ? "block" : "none";
    });
}






</script>