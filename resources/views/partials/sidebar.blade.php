<!-- BOTON SIDEBAR -->
<button
    id="menuBtnsidebar"
    class="menu-toggle"
    style="
        position:fixed;
        top:10px;
        left:0px;

    ">
    ☰

</button>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">

    <img src="{{ asset('imagenes/logo.png') }}" alt="Logo">

    <!-- MENU -->
    <nav class="sidebar-menu">

        <a href="{{ route('dashboard') }}"
           class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
            🏠 Dashboard
        </a>

        <a href="{{ route('sales.index') }}"
           class="sidebar-link {{ request()->is('ventas*') ? 'active' : '' }}">
            💰 Ventas
        </a>

        <a href="{{ route('catalog.index') }}"
           class="sidebar-link {{ request()->is('catalogo*') ? 'active' : '' }}">
            📦 Catálogo
        </a>

        <a href="{{ route('inventory.index') }}"
           class="sidebar-link {{ request()->is('inventario*') ? 'active' : '' }}">
            📊 Inventario
        </a>

        <a href="{{ route('payments.index') }}"
           class="sidebar-link {{ request()->is('pagos*') ? 'active' : '' }}">
            💳 Pagos
        </a>

        <a href="{{ route('customers') }}"
           class="sidebar-link {{ request()->is('cliente*') ? 'active' : '' }}">
            👥 Clientes
        </a>

        <a href="{{ route('reports.index') }}"
           class="sidebar-link {{ request()->is('reportes*') ? 'active' : '' }}">
            📈 Reportes
        </a>

        <a href="{{ route('settings') }}"
           class="sidebar-link {{ request()->is('configuracion*') ? 'active' : '' }}">
            ⚙️ Configuración
        </a>

        <a href="{{ route('portal.cliente') }}"
           class="sidebar-link {{ request()->is('portal-cliente*') ? 'active' : '' }}">
            👤 Portal del cliente
        </a>

    </nav>

    <!-- AYUDA -->
    <div class="help-sidebar">

        <h4>
            ¿Necesitas ayuda?
        </h4>

        <p>
            Centro de ayuda
        </p>

        <button
            type="button"
            onclick="abrirAyuda()">

            Abrir ayuda

        </button>

    </div>

</aside>

<!-- MODAL -->
<div
    id="modalAyuda"
    class="modal-ayuda">

    <div class="help-modal">

        <!-- HEADER -->
        <div class="help-header">

            <div>

                <h2>
                    Centro de ayuda
                </h2>

                <p>
                    Estamos aquí para ayudarte
                </p>

            </div>

            <button
                class="close-help"
                onclick="cerrarAyuda()">

                ✕

            </button>

        </div>

        <!-- BODY -->
        <div class="help-body">

            <!-- SEARCH -->
            <div class="help-search">

                <input
                    type="text"
                    id="buscadorFAQ"
                    placeholder="Busca cómo hacer una venta, abrir caja, subir productos..."
                    onkeyup="buscarFAQ()">

            </div>

            <h4 class="help-title">
                Artículos más consultados
            </h4>

            <!-- FAQ -->
            <div class="help-list faq">

                <!-- ITEM -->
                <div
                    class="help-item item"
                    onclick="toggleFAQ(this)">

                    <div class="help-info">

                        <strong>
                            Cómo registrar una venta
                        </strong>

                        <span>
                            Ventas
                        </span>

                    </div>

                    <span class="arrow">›</span>

                    <p class="help-text">
                        Ve al módulo de ventas, selecciona productos y confirma el pago.
                    </p>

                </div>

                <!-- ITEM -->
                <div
                    class="help-item item"
                    onclick="toggleFAQ(this)">

                    <div class="help-info">

                        <strong>
                            Cómo agregar productos al catálogo
                        </strong>

                        <span>
                            Catálogo
                        </span>

                    </div>

                    <span class="arrow">›</span>

                    <p class="help-text">
                        En la sección catálogo puedes registrar nuevos productos.
                    </p>

                </div>

                <!-- ITEM -->
                <div
                    class="help-item item active"
                    onclick="toggleFAQ(this)">

                    <div class="help-info">

                        <strong>
                            Cómo hacer un ajuste de inventario
                        </strong>

                        <span>
                            Inventario
                        </span>

                    </div>

                    <span class="arrow">›</span>

                    <p class="help-text">
                        Accede al inventario y ajusta existencias manualmente.
                    </p>

                </div>

                <!-- ITEM -->
                <div
                    class="help-item item"
                    onclick="toggleFAQ(this)">

                    <div class="help-info">

                        <strong>
                            Cómo generar reportes
                        </strong>

                        <span>
                            Reportes
                        </span>

                    </div>

                    <span class="arrow">›</span>

                    <p class="help-text">
                        Ve a reportes para visualizar estadísticas y ventas.
                    </p>

                </div>

            </div>

            <!-- ASISTENTE -->
            <div class="assistant-card">

                <div class="assistant-icon">
                    ✨
                </div>

                <div>

                    <strong>
                        Pregunta al asistente
                    </strong>

                    <p>
                        Resuelve tus dudas paso a paso con ayuda inteligente
                    </p>

                </div>

                <button
                    type="button"
                    onclick="mostrarSoporte()">

                    💬 Iniciar chat

                </button>

            </div>

            <!-- FOOTER -->
            <div class="help-footer">

                <p>
                    ¿No encontraste lo que buscas?
                </p>

                <span>
                    Contacta a nuestro equipo de soporte y te ayudaremos personalmente
                </span>

            </div>

        </div>

        <!-- SOPORTE -->
        <div
            id="soporteView"
            class="support-view">

            <!-- VOLVER -->
            <button
                class="back-btn"
                onclick="volverAyuda()">

                ← Volver

            </button>

            <!-- ICON -->
            <div class="support-icon">
                ✉️
            </div>

            <!-- TITLE -->
            <h2>
                Contactar soporte
            </h2>

            <p class="support-subtitle">
                Nuestro equipo de soporte te responderá lo antes posible
            </p>

            <!-- FORMULARIO -->
            <form
                method="POST"
                action="{{ route('support.ticket') }}">

                @csrf

                <!-- ASUNTO -->
                <div class="support-group">

                    <label>
                        Asunto *
                    </label>

                    <input
                        type="text"
                        name="subject"
                        placeholder="Ej: Problema al cerrar caja"
                        required>

                </div>

                <!-- MENSAJE -->
                <div class="support-group">

                    <label>
                        Mensaje *
                    </label>

                    <textarea
                        rows="6"
                        name="message"
                        placeholder="Describe tu problema o duda con el mayor detalle posible..."
                        required></textarea>

                </div>

                <!-- BOTONES -->
                <div class="support-actions">

                    <button
                        type="button"
                        class="support-cancel"
                        onclick="volverAyuda()">

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="support-send">

                        Enviar mensaje

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- OVERLAY -->
<div id="overlay" class="overlay"></div>

<!-- JS -->
<script>

const btn = document.getElementById('menuBtnsidebar');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

/* =========================
   SIDEBAR
========================= */

btn.addEventListener('click', () => {

    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');

});

overlay.addEventListener('click', () => {

    sidebar.classList.remove('active');
    overlay.classList.remove('active');

});



function abrirAyuda() {

    document.getElementById("modalAyuda").style.display = "flex";

}

function cerrarAyuda() {

    document.getElementById("modalAyuda").style.display = "none";

}



function toggleFAQ(item) {

    item.classList.toggle("open");

}



function buscarFAQ() {

    let input = document
        .getElementById("buscadorFAQ")
        .value
        .toLowerCase();

    let items = document.querySelectorAll(".faq .item");

    items.forEach(item => {

        let texto = item.innerText.toLowerCase();

        item.style.display =
            texto.includes(input)
            ? "block"
            : "none";

    });

}



function mostrarSoporte(){

    document.querySelector(".help-body").style.display = "none";

    document
        .getElementById("soporteView")
        .classList.add("active");

}

function volverAyuda(){

    document.querySelector(".help-body").style.display = "block";

    document
        .getElementById("soporteView")
        .classList.remove("active");

}



window.onclick = function(e) {

    let modal = document.getElementById("modalAyuda");

    if (e.target === modal) {

        cerrarAyuda();

    }

};

</script>