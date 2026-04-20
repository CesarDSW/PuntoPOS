@extends('layout.dashboard_design')

@section('title', 'Ventas')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/sales/index.css') }}">
@endpush

@section('content')

<div class="sales-wrap">
    <div class="sales-header">
        <div>
            <h1 style="font-size:32px; margin-bottom:8px;">Ventas</h1>
            <p class="text-muted">Gestiona todas las ventas de tu negocio.</p>
        </div>

        <div class="sales-actions">
            <a href="{{ route('sales.cash.history') }}" class="btn btn-dark">Historial de cajas</a>
            <button type="button" id="posToggleButton" class="btn btn-dark">Abrir POS</button>
            <a href="{{ route('sales.pos') }}" class="btn btn-primary">Nueva venta</a>
        </div>
    </div>

    <div class="filters-card">
        <input type="text" id="salesSearch" class="input" placeholder="Buscar por ID, cliente...">
        <input type="date" id="salesDate" class="input">
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

<script>
    const salesPosUrl = @json(route('sales.pos'));

    function money(value) {
        return window.appFormat.money(value);
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
        btn.textContent = 'Abrir POS';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-dark');
        btn.onclick = () => {
            window.location.href = salesPosUrl;
        };
    }

    function configurePosButtonAsClose() {
        const btn = document.getElementById('posToggleButton');
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

    async function loadSales() {
        const search = document.getElementById('salesSearch').value.trim();
        const date = document.getElementById('salesDate').value;
        const status = document.getElementById('salesStatus').value;

        const params = new URLSearchParams({ per_page: 50, status });

        if (search) params.append('search', search);
        if (date) params.append('date', date);

        const { response, data } = await apiFetch(`/api/sales?${params.toString()}`);
        const tbody = document.getElementById('salesTableBody');

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
            let badge = '<span class="badge badge-blue">Pagada</span>';
            if (item.status_sale === 'PENDIENTE') badge = '<span class="badge badge-yellow">Pendiente</span>';
            if (item.status_sale === 'CANCELADA') badge = '<span class="badge badge-red">Cancelada</span>';

            return `
                <tr>
                    <td style="font-weight:700; color:#1d4ed8;">${item.sale_folio}</td>
                    <td>${window.appFormat.dateTime(item.date_time)}</td>
                    <td style="font-weight:600;">${item.customer_name}</td>
                    <td>${item.items_count} items</td>
                    <td style="font-weight:700;">${money(item.total)}</td>
                    <td>${item.payment_method ?? '-'}</td>
                    <td>${badge}</td>
                    <td>
                        <a href="/ventas/${item.sale_id}" class="icon-btn" title="Ver detalle">👁️</a>
                    </td>
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

        document.getElementById('salesSearch').addEventListener('input', loadSales);
        document.getElementById('salesDate').addEventListener('change', loadSales);
        document.getElementById('salesStatus').addEventListener('change', loadSales);

        const closePosOverlay = document.getElementById('closePosOverlay');
        const closePosAmount = document.getElementById('closePosAmount');

        closePosOverlay.addEventListener('click', function (e) {
            if (e.target === closePosOverlay) {
                closeClosePosModal();
            }
        });

        closePosAmount.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                confirmClosePos();
            }
        });
    });
</script>
@endsection