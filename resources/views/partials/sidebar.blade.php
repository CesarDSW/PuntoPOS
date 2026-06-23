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

    <div class="sidebar-logo">
        <img src="{{ asset('assets/img/Punto_logo-blanco.png') }}" alt="Punto POS">
    </div>
    
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
        <div id="soporteView" class="support-view">
            <button
                class="back-btn"
                onclick="volverAyuda()">
                ← Volver
            </button>

            <div class="support-tabs">
                <button type="button" class="support-tab active" onclick="mostrarNuevoTicket()">
                    Nuevo mensaje
                </button>

                <button type="button" class="support-tab" onclick="mostrarMisTickets()">
                    Mis conversaciones
                </button>
            </div>

            <div id="nuevoTicketView">
                <div class="support-icon">
                    ✉️
                </div>

                <h2>
                    Contactar soporte
                </h2>

                <p class="support-subtitle">
                    Nuestro equipo de soporte te responderá lo antes posible
                </p>

                <form
                    method="POST"
                    action="{{ route('support.ticket') }}">

                    @csrf

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

            <div id="misTicketsView" class="support-conversations-view" style="display:none;">
                <div class="support-conversations-header">
                    <h2>Mis conversaciones</h2>
                    <p>Continúa una conversación con soporte.</p>
                </div>

                <div id="supportTicketsList" class="support-tickets-list">
                    <p class="support-loading">Cargando conversaciones...</p>
                </div>

                <div id="supportConversationBox" class="support-conversation-box" style="display:none;">
                    <button type="button" class="back-btn" onclick="volverListaTickets()">
                        ← Volver a mis conversaciones
                    </button>

                    <h3 id="conversationSubject"></h3>

                    <div id="conversationMessages" class="conversation-messages"></div>

                    <form id="replySupportForm" class="conversation-reply-form no-global-loader" data-no-loader="true">
                        @csrf

                        <textarea
                            id="replySupportMessage"
                            rows="4"
                            placeholder="Escribe tu respuesta..."
                            required></textarea>

                        <button type="submit" class="support-send">
                            Enviar respuesta
                        </button>
                    </form>
                </div>
            </div>
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

let currentSupportTicketId = null;

/* =========================
   SIDEBAR
========================= */
btn?.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
});

overlay?.addEventListener('click', () => {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
});

/* =========================
   MODAL
========================= */
function abrirAyuda() {
    document.getElementById("modalAyuda").style.display = "flex";
}

function cerrarAyuda() {
    document.getElementById("modalAyuda").style.display = "none";
}

/* =========================
   FAQ
========================= */
function toggleFAQ(item) {
    item.classList.toggle("open");
}

/* =========================
   SEARCH FAQ
========================= */
function buscarFAQ() {
    const buscador = document.getElementById("buscadorFAQ");

    if (!buscador) {
        return;
    }

    let input = buscador.value.toLowerCase();
    let items = document.querySelectorAll(".faq .item");

    items.forEach(item => {
        let texto = item.innerText.toLowerCase();

        item.style.display =
            texto.includes(input)
            ? "block"
            : "none";
    });
}

/* =========================
   SOPORTE
========================= */
function mostrarSoporte() {
    const helpBody = document.querySelector(".help-body");
    const soporteView = document.getElementById("soporteView");

    if (!helpBody || !soporteView) {
        return;
    }

    helpBody.style.display = "none";
    soporteView.classList.add("active");

    mostrarNuevoTicket();

    const urlParams = new URLSearchParams(window.location.search);
    const ticketId = urlParams.get('support_ticket');

    if (ticketId) {
        mostrarMisTickets();
        abrirConversacionSoporte(ticketId);
    }
}

function volverAyuda() {
    const helpBody = document.querySelector(".help-body");
    const soporteView = document.getElementById("soporteView");

    if (!helpBody || !soporteView) {
        return;
    }

    helpBody.style.display = "block";
    soporteView.classList.remove("active");
}

function mostrarNuevoTicket() {
    const nuevoTicketView = document.getElementById('nuevoTicketView');
    const misTicketsView = document.getElementById('misTicketsView');

    if (!nuevoTicketView || !misTicketsView) {
        return;
    }

    nuevoTicketView.style.display = 'block';
    misTicketsView.style.display = 'none';

    document.querySelectorAll('.support-tab').forEach(btn => btn.classList.remove('active'));

    const tabs = document.querySelectorAll('.support-tab');

    if (tabs[0]) {
        tabs[0].classList.add('active');
    }
}

function mostrarMisTickets() {
    const nuevoTicketView = document.getElementById('nuevoTicketView');
    const misTicketsView = document.getElementById('misTicketsView');
    const supportConversationBox = document.getElementById('supportConversationBox');
    const supportTicketsList = document.getElementById('supportTicketsList');

    if (!nuevoTicketView || !misTicketsView || !supportConversationBox || !supportTicketsList) {
        return;
    }

    nuevoTicketView.style.display = 'none';
    misTicketsView.style.display = 'block';
    supportConversationBox.style.display = 'none';
    supportTicketsList.style.display = 'block';

    document.querySelectorAll('.support-tab').forEach(btn => btn.classList.remove('active'));

    const tabs = document.querySelectorAll('.support-tab');

    if (tabs[1]) {
        tabs[1].classList.add('active');
    }

    cargarMisTickets();
}

function cargarMisTickets() {
    const list = document.getElementById('supportTicketsList');

    if (!list) {
        return;
    }

    list.innerHTML = '<p class="support-loading">Cargando conversaciones...</p>';

    fetch("{{ route('support.conversations') }}", {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.tickets || data.tickets.length === 0) {
            list.innerHTML = `
                <div class="support-empty-mini">
                    <strong>No tienes conversaciones todavía</strong>
                    <span>Cuando envíes una duda, aparecerá aquí.</span>
                </div>
            `;
            return;
        }

        list.innerHTML = data.tickets.map(ticket => `
            <button type="button" class="support-ticket-item" onclick="abrirConversacionSoporte(${ticket.id})">
                <div>
                    <strong>${escapeHtml(ticket.subject)}</strong>
                    <span>${escapeHtml(ticket.message ?? '')}</span>
                    <small>${ticket.created_at}</small>
                </div>

                <div class="support-ticket-meta">
                    <span class="support-ticket-status">${escapeHtml(ticket.status)}</span>
                    ${
                        ticket.unread_messages_count > 0
                        ? `<span class="support-ticket-unread">${ticket.unread_messages_count}</span>`
                        : ''
                    }
                </div>
            </button>
        `).join('');
    })
    .catch(() => {
        list.innerHTML = '<p class="support-error">No se pudieron cargar tus conversaciones.</p>';
    });
}

function abrirConversacionSoporte(ticketId) {
    currentSupportTicketId = ticketId;

    fetch(`/support/conversaciones/${ticketId}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const supportTicketsList = document.getElementById('supportTicketsList');
        const supportConversationBox = document.getElementById('supportConversationBox');
        const conversationSubject = document.getElementById('conversationSubject');
        const messagesBox = document.getElementById('conversationMessages');

        if (!supportTicketsList || !supportConversationBox || !conversationSubject || !messagesBox) {
            return;
        }

        supportTicketsList.style.display = 'none';
        supportConversationBox.style.display = 'block';

        conversationSubject.innerText = data.ticket.subject;

        messagesBox.innerHTML = data.messages.map(message => `
            <div class="conversation-message ${message.is_mine ? 'mine' : 'other'}">
                <div class="conversation-bubble">
                    <div class="conversation-head">
                        <strong>${escapeHtml(message.sender_name)}</strong>
                        <span>${message.created_at}</span>
                    </div>

                    <p>${escapeHtml(message.message)}</p>
                </div>
            </div>
        `).join('');

        messagesBox.scrollTop = messagesBox.scrollHeight;
    })
    .catch(() => {
        alert('No se pudo abrir la conversación.');
    });
}

function volverListaTickets() {
    const supportConversationBox = document.getElementById('supportConversationBox');
    const supportTicketsList = document.getElementById('supportTicketsList');

    if (!supportConversationBox || !supportTicketsList) {
        return;
    }

    supportConversationBox.style.display = 'none';
    supportTicketsList.style.display = 'block';

    cargarMisTickets();
}

document.getElementById('replySupportForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    if (!currentSupportTicketId) {
        return;
    }

    const textarea = document.getElementById('replySupportMessage');
    const message = textarea.value.trim();

    if (!message) {
        return;
    }

    fetch(`/support/conversaciones/${currentSupportTicketId}/responder`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            message: message
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('No se pudo enviar el mensaje.');
        }

        textarea.value = '';
        abrirConversacionSoporte(currentSupportTicketId);
    })
    .catch(() => {
        alert('No se pudo enviar tu respuesta.');
    });
});

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

/* =========================
   CLICK FUERA MODAL
========================= */
window.onclick = function(e) {
    let modal = document.getElementById("modalAyuda");

    if (e.target === modal) {
        cerrarAyuda();
    }
};
</script>