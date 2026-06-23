@extends('layout.dashboard_design')

@section('title', 'Ventas')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/sales/index.css') }}">

    <style>
        .sale-actions-dropdown {
            position: relative;
            display: inline-flex;
            justify-content: center;
        }

        .sale-actions-toggle {
            min-width: 98px;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid rgba(29, 78, 216, .25);
            background: #0f172a;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
        }

        .sale-actions-toggle:hover {
            background: #1d4ed8;
        }

        .sale-actions-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            min-width: 170px;
            display: none;
            z-index: 40;
            padding: 8px;
            background: var(--panel-bg, #fff);
            color: var(--panel-text, #0f172a);
            border: 1px solid var(--panel-border, #e5e7eb);
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .22);
        }

        .sale-actions-menu.show {
            display: grid;
            gap: 6px;
        }

        .sale-actions-menu a,
        .sale-actions-menu button {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 0;
            border-radius: 10px;
            padding: 10px 12px;
            background: transparent;
            color: inherit;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
            text-align: left;
        }

        .sale-actions-menu a:hover,
        .sale-actions-menu button:hover {
            background: var(--panel-bg-soft, #f1f5f9);
        }

        .sale-actions-menu .danger-action {
            color: #dc2626;
        }

        .sales-date-filter {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sales-date-filter label {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
        }
    </style>
@endpush

@section('content')

@php
    $salesAccess = [
        'view' => \App\Support\UserAccess::has(auth()->user(), 'sales.view'),
        'create' => \App\Support\UserAccess::has(auth()->user(), 'sales.create'),
        'pos_use' => \App\Support\UserAccess::has(auth()->user(), 'sales.pos.use'),
        'ticket_print' => \App\Support\UserAccess::has(auth()->user(), 'sales.ticket.print'),
        'cash_open' => \App\Support\UserAccess::has(auth()->user(), 'cash.open'),
        'cash_close' => \App\Support\UserAccess::has(auth()->user(), 'cash.close'),
        'cash_history' => \App\Support\UserAccess::has(auth()->user(), 'cash.history.view'),
    ];
@endphp

<div class="sales-wrap">
    <div class="sales-header">
        <div>
            <h1 style="font-size:32px; margin-bottom:8px;">Ventas</h1>
            <p class="text-muted">Gestiona todas las ventas de tu negocio.</p>
        </div>

        <div class="sales-actions">
            @if($salesAccess['cash_history'])
                <a href="{{ route('sales.cash.history') }}" class="btn btn-dark">Historial de cajas</a>
            @endif

            @if($salesAccess['pos_use'] || $salesAccess['cash_close'])
                <button type="button" id="posToggleButton" class="btn btn-dark">Abrir POS</button>
            @endif

            @if($salesAccess['create'])
                <a href="{{ route('sales.pos') }}" class="btn btn-primary">Nueva venta</a>
            @endif
        </div>
    </div>

    <div class="filters-card">
        <input type="text" id="salesSearch" class="input" placeholder="Buscar por ID, cliente...">

        <div class="sales-date-filter">
            <label for="salesDateFrom">Fecha inicio</label>
            <input type="date" id="salesDateFrom" class="input">
        </div>

        <div class="sales-date-filter">
            <label for="salesDateTo">Fecha final</label>
            <input type="date" id="salesDateTo" class="input">
        </div>

        <select id="salesStatus" class="select">
            <option value="all">Todos los estados</option>
            <option value="PAGADA">Pagada</option>
            <option value="PENDIENTE">Pendiente</option>
            <option value="CANCELADA">Cancelada</option>
        </select>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Total vendido hoy</div>
            <div class="summary-value" id="sumTotalSold">$0</div>
            <div class="summary-note">Ventas del día en la sucursal actual</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Ventas realizadas</div>
            <div class="summary-value" id="sumSalesCount">0</div>
            <div class="summary-note">En las últimas 24 horas</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Ticket promedio</div>
            <div class="summary-value" id="sumAvgTicket">$0</div>
            <div class="summary-note">Promedio del día</div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">Historial de ventas</div>
                <div class="card-subtitle" id="salesCountText">0 ventas registradas</div>
            </div>
        </div>

        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID Venta</th>
                        <th>Fecha y hora</th>
                        <th>Cliente</th>
                        <th>Productos</th>
                        <th>Total</th>
                        <th>Método de pago</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="salesTableBody">
                    <tr><td colspan="8" class="empty-box">Cargando ventas...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="overlay" id="closePosOverlay">
    <div class="modal">
        <div class="modal-head">
            <div>
                <div style="font-size:24px; font-weight:700;">Cerrar POS</div>
                <div style="color:#64748b; margin-top:6px;">Ingresa el monto final de caja para cerrar el punto de venta</div>
            </div>
            <button type="button" class="btn" onclick="closeClosePosModal()">Cerrar</button>
        </div>

        <div class="modal-body">
            <div class="info-card">
                <div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:8px;">
                    <span style="color:#64748b;">Acción</span>
                    <strong>Cierre de caja / POS</strong>
                </div>
                <div style="display:flex; justify-content:space-between; gap:12px;">
                    <span style="color:#64748b;">Estado</span>
                    <strong>Listo para cerrar</strong>
                </div>
            </div>

            <div class="field">
                <label for="closePosAmount" class="label">Monto final de caja</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    id="closePosAmount"
                    class="input"
                    placeholder="0.00"
                >
            </div>

            <div class="field">
                <label for="closePosNotes" class="label">Observaciones (opcional)</label>
                <textarea
                    id="closePosNotes"
                    class="textarea"
                    placeholder="Ej: sobrante, faltante, corte revisado..."
                ></textarea>
            </div>

            <div class="error-box" id="closePosErrorBox"></div>
        </div>

        <div class="modal-foot">
            <button type="button" class="btn" onclick="closeClosePosModal()">Cancelar</button>
            <button type="button" class="btn btn-primary" id="confirmClosePosButton" onclick="confirmClosePos()">Confirmar cierre</button>
        </div>
    </div>
</div>

<div class="overlay" id="cancelSaleOverlay">
    <div class="modal modal-confirm">
        <div class="modal-head">
            <div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <div class="danger-icon">✖</div>
                    <div>
                        <div style="font-size:24px; font-weight:700;">Cancelar venta</div>
                        <div style="color:#64748b; margin-top:6px;">Esta acción cancelará la venta pendiente seleccionada.</div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn" onclick="closeCancelSaleModal()">Cerrar</button>
        </div>

        <div class="modal-body">
            <div class="info-card danger-card">
                <div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:8px;">
                    <span style="color:#64748b;">Acción</span>
                    <strong>Cancelar venta</strong>
                </div>

                <div style="display:flex; justify-content:space-between; gap:12px;">
                    <span style="color:#64748b;">Estado actual</span>
                    <strong>Pendiente</strong>
                </div>
            </div>

            <div class="confirm-text" id="cancelSaleText">
                ¿Deseas cancelar esta venta pendiente?
            </div>

            <div class="confirm-note">
                La venta se marcará como cancelada y dejará de estar disponible para continuar.
            </div>

            <div class="error-box" id="cancelSaleErrorBox"></div>
        </div>

        <div class="modal-foot">
            <button type="button" class="btn" onclick="closeCancelSaleModal()">Conservar venta</button>
            <button type="button" class="btn btn-primary" id="confirmCancelSaleButton" onclick="confirmCancelPendingSale()">
                Sí, cancelar venta
            </button>
        </div>
    </div>
</div>

<script>
    const salesPosUrl = @json(route('sales.pos'));
    const salesAccess = @json($salesAccess);
    let pendingSaleToCancel = null;

    function money(value) {
        return window.appFormat.money(value);
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showError(id, message) {
        const box = document.getElementById(id);
        box.textContent = message;
        box.style.display = 'block';
    }

    function hideError(id) {
        const box = document.getElementById(id);
        box.textContent = '';
        box.style.display = 'none';
    }

    async function apiFetch(url, options = {}) {
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.headers || {})
                }
            });

            const text = await response.text();
            let data = {};

            try {
                data = text ? JSON.parse(text) : {};
            } catch (e) {
                data = { message: text || 'Respuesta inválida.' };
            }

            return { response, data };
        } catch (error) {
            return {
                response: { ok: false, status: 0 },
                data: { message: error.message || 'Error de red.' }
            };
        }
    }

    function configurePosButtonAsOpen() {
        const btn = document.getElementById('posToggleButton');
        if (!btn) return;

        if (!salesAccess.pos_use) {
            btn.style.display = 'none';
            return;
        }

        btn.style.display = '';
        btn.textContent = 'Abrir POS';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-dark');
        btn.onclick = () => {
            window.location.href = salesPosUrl;
        };
    }

    function configurePosButtonAsClose() {
        const btn = document.getElementById('posToggleButton');
        if (!btn) return;

        if (!salesAccess.cash_close) {
            configurePosButtonAsOpen();
            return;
        }

        btn.style.display = '';
        btn.textContent = 'Cerrar POS';
        btn.classList.remove('btn-dark');
        btn.classList.add('btn-primary');
        btn.onclick = openClosePosModal;
    }

    async function loadPosButtonState() {
        const { response, data } = await apiFetch('/api/sales/pos/status');

        if (!response.ok || !data.cash_session) {
            configurePosButtonAsOpen();
            return;
        }

        configurePosButtonAsClose();
    }

    function openClosePosModal() {
        hideError('closePosErrorBox');
        document.getElementById('closePosAmount').value = '';
        document.getElementById('closePosNotes').value = '';
        document.getElementById('closePosOverlay').classList.add('show');

        setTimeout(() => {
            document.getElementById('closePosAmount').focus();
        }, 50);
    }

    function closeClosePosModal() {
        document.getElementById('closePosOverlay').classList.remove('show');
    }

    async function confirmClosePos() {
        hideError('closePosErrorBox');

        const closingAmount = document.getElementById('closePosAmount').value.trim();
        const notesClosing = document.getElementById('closePosNotes').value.trim();
        const button = document.getElementById('confirmClosePosButton');

        if (closingAmount === '' || Number(closingAmount) < 0) {
            showError('closePosErrorBox', 'Ingresa un monto final válido.');
            return;
        }

        button.disabled = true;
        button.textContent = 'Cerrando...';

        const { response, data } = await apiFetch('/api/sales/cash/close', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                closing_amount: closingAmount,
                notes_closing: notesClosing
            })
        });

        button.disabled = false;
        button.textContent = 'Confirmar cierre';

        if (!response.ok) {
            showError(
                'closePosErrorBox',
                data.errors?.shift?.[0] ||
                data.errors?.closing_amount?.[0] ||
                data.message ||
                'No se pudo cerrar el POS.'
            );
            return;
        }

        closeClosePosModal();
        window.location.href = data.redirect_url || ('/ventas/cajas/' + data.cash_session_id);
    }

    async function loadSummary() {
        const { response, data } = await apiFetch('/api/sales/summary');
        if (!response.ok) return;

        document.getElementById('sumTotalSold').textContent = money(data.total_sold_today ?? 0);
        document.getElementById('sumSalesCount').textContent = data.sales_last_24h ?? 0;
        document.getElementById('sumAvgTicket').textContent = money(data.avg_ticket_today ?? 0);
    }

    function getStatusBadge(status) {
        if (status === 'PENDIENTE') {
            return '<span class="badge badge-yellow">Pendiente</span>';
        }

        if (status === 'CANCELADA') {
            return '<span class="badge badge-red">Cancelada</span>';
        }

        return '<span class="badge badge-blue">Pagada</span>';
    }

    function buildActionButtons(item) {
        const saleId = Number(item.sale_id);
        const menuId = `saleActionMenu${saleId}`;

        if (item.status_sale === 'PENDIENTE') {
            return `
                <div class="sale-actions-dropdown">
                    <button
                        type="button"
                        class="sale-actions-toggle"
                        onclick="toggleSaleActions(event, ${saleId})"
                    >Acciones ▾</button>

                    <div class="sale-actions-menu" id="${menuId}">
                        <button type="button" onclick="resumePendingSale(${saleId})">↩️ Continuar venta</button>
                        <button type="button" class="danger-action" onclick="cancelPendingSale(${saleId})">✖️ Cancelar venta</button>
                    </div>
                </div>
            `;
        }

        return `
            <div class="sale-actions-dropdown">
                <button
                    type="button"
                    class="sale-actions-toggle"
                    onclick="toggleSaleActions(event, ${saleId})"
                >Acciones ▾</button>

                <div class="sale-actions-menu" id="${menuId}">
                    <a href="/ventas/${saleId}/factura" target="_blank">🧾 Ver factura</a>
                    <a href="/ventas/${saleId}/ticket" target="_blank">🎫 Ver ticket</a>
                </div>
            </div>
        `;
    }

    function toggleSaleActions(event, saleId) {
        event.stopPropagation();

        const currentMenu = document.getElementById(`saleActionMenu${saleId}`);

        document.querySelectorAll('.sale-actions-menu.show').forEach(menu => {
            if (menu !== currentMenu) {
                menu.classList.remove('show');
            }
        });

        if (currentMenu) {
            currentMenu.classList.toggle('show');
        }
    }

    function closeSaleActionMenus() {
        document.querySelectorAll('.sale-actions-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    }

    function resumePendingSale(saleId) {
        window.location.href = `${salesPosUrl}?sale_id=${saleId}`;
    }

    function cancelPendingSale(saleId) {
        pendingSaleToCancel = saleId;
        hideError('cancelSaleErrorBox');
        document.getElementById('cancelSaleText').textContent = `¿Deseas cancelar la venta pendiente #${saleId}?`;
        document.getElementById('cancelSaleOverlay').classList.add('show');
    }

    function closeCancelSaleModal() {
        pendingSaleToCancel = null;
        hideError('cancelSaleErrorBox');
        document.getElementById('cancelSaleOverlay').classList.remove('show');
    }

    async function confirmCancelPendingSale() {
        hideError('cancelSaleErrorBox');

        if (!pendingSaleToCancel) {
            showError('cancelSaleErrorBox', 'No hay ninguna venta seleccionada para cancelar.');
            return;
        }

        const button = document.getElementById('confirmCancelSaleButton');
        button.disabled = true;
        button.textContent = 'Cancelando...';

        const { response, data } = await apiFetch(`/api/sales/${pendingSaleToCancel}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        button.disabled = false;
        button.textContent = 'Sí, cancelar venta';

        if (!response.ok) {
            showError(
                'cancelSaleErrorBox',
                data.errors?.sale?.[0] ||
                data.message ||
                'No se pudo cancelar la venta pendiente.'
            );
            return;
        }

        closeCancelSaleModal();
        await loadSummary();
        await loadSales();
    }

    async function loadSales() {
        const search = document.getElementById('salesSearch').value.trim();
        const dateFrom = document.getElementById('salesDateFrom').value;
        const dateTo = document.getElementById('salesDateTo').value;
        const status = document.getElementById('salesStatus').value;
        const tbody = document.getElementById('salesTableBody');

        if (dateFrom && dateTo && dateFrom > dateTo) {
            tbody.innerHTML = `<tr><td colspan="8" class="empty-box">La fecha inicio no puede ser mayor que la fecha final.</td></tr>`;
            document.getElementById('salesCountText').textContent = '0 ventas registradas';
            return;
        }

        const params = new URLSearchParams({ per_page: 50, status });

        if (search) params.append('search', search);
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);

        const { response, data } = await apiFetch(`/api/sales?${params.toString()}`);

        if (!response.ok) {
            tbody.innerHTML = `<tr><td colspan="8" class="empty-box">No se pudieron cargar las ventas.</td></tr>`;
            return;
        }

        const items = data.data || [];
        document.getElementById('salesCountText').textContent = `${items.length} ventas registradas`;

        if (items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="empty-box">No hay ventas para mostrar.</td></tr>`;
            return;
        }

        tbody.innerHTML = items.map(item => {
            const formattedDate = item.date_time_display || window.appFormat.dateTime(item.date_time);

            return `
                <tr>
                    <td style="font-weight:700; color:#1d4ed8;">${escapeHtml(item.sale_folio)}</td>
                    <td>${escapeHtml(formattedDate)}</td>
                    <td style="font-weight:600;">${escapeHtml(item.customer_name || '-')}</td>
                    <td>${Number(item.items_count || 0)} items</td>
                    <td style="font-weight:700;">${money(item.total)}</td>
                    <td>${escapeHtml(item.payment_method ?? '-')}</td>
                    <td>${getStatusBadge(item.status_sale)}</td>
                    <td>${buildActionButtons(item)}</td>
                </tr>
            `;
        }).join('');
    }

    async function loadPage() {
        await loadPosButtonState();
        await loadSummary();
        await loadSales();
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadPage();

        document.addEventListener('click', closeSaleActionMenus);

        document.getElementById('salesSearch').addEventListener('input', loadSales);
        document.getElementById('salesDateFrom').addEventListener('change', loadSales);
        document.getElementById('salesDateTo').addEventListener('change', loadSales);
        document.getElementById('salesStatus').addEventListener('change', loadSales);

        const closePosOverlay = document.getElementById('closePosOverlay');
        const cancelSaleOverlay = document.getElementById('cancelSaleOverlay');
        const closePosAmount = document.getElementById('closePosAmount');

        closePosOverlay.addEventListener('click', function (e) {
            if (e.target === closePosOverlay) {
                closeClosePosModal();
            }
        });

        cancelSaleOverlay.addEventListener('click', function (e) {
            if (e.target === cancelSaleOverlay) {
                closeCancelSaleModal();
            }
        });

        closePosAmount.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                confirmClosePos();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeClosePosModal();
                closeCancelSaleModal();
            }
        });
    });
</script>
@endsection
