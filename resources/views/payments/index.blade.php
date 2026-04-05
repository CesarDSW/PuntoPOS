@extends('layouts.app')

@section('title', 'Pagos')

@section('content')
<style>
    .payments-wrap { display:flex; flex-direction:column; gap:18px; }
    .payments-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; }
    .payments-actions { display:flex; gap:10px; flex-wrap:wrap; }

    .summary-grid {
        display:grid;
        grid-template-columns:repeat(4, minmax(0, 1fr));
        gap:16px;
    }

    .summary-card, .filters-card, .card {
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius:16px;
    }

    .summary-card { padding:20px; }
    .summary-label { color:#64748b; font-size:14px; margin-bottom:10px; }
    .summary-value { font-size:22px; font-weight:700; }
    .summary-note { margin-top:8px; color:#64748b; font-size:13px; }

    .filters-card {
        padding:16px;
        display:grid;
        grid-template-columns:1.4fr 0.8fr 0.8fr;
        gap:12px;
    }

    .input, .select {
        width:100%;
        border:1px solid #d1d5db;
        border-radius:12px;
        padding:12px 14px;
        background:#fff;
        font-size:14px;
    }

    .card-head {
        padding:20px;
        border-bottom:1px solid #e5e7eb;
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
    }

    .card-title { font-size:18px; font-weight:700; }
    .card-subtitle { color:#64748b; font-size:14px; margin-top:4px; }

    table { width:100%; border-collapse:collapse; }
    th, td {
        padding:14px 16px;
        border-bottom:1px solid #e5e7eb;
        text-align:left;
        font-size:14px;
        vertical-align:middle;
    }
    th {
        background:#f8fafc;
        font-size:12px;
        color:#64748b;
        text-transform:uppercase;
    }

    .badge {
        display:inline-block;
        padding:5px 10px;
        border-radius:999px;
        font-size:12px;
        font-weight:700;
    }

    .badge-green { background:#dcfce7; color:#166534; }
    .badge-yellow { background:#fef3c7; color:#92400e; }
    .badge-red { background:#fee2e2; color:#b91c1c; }

    .icon-btn {
        border:none;
        background:transparent;
        cursor:pointer;
        color:#1d4ed8;
        font-size:18px;
        text-decoration:none;
    }

    .empty-box {
        padding:22px;
        text-align:center;
        color:#64748b;
    }

    @media (max-width: 1100px) {
        .summary-grid { grid-template-columns:1fr; }
        .filters-card { grid-template-columns:1fr; }
        .payments-header { flex-direction:column; align-items:stretch; }
    }
</style>

<div class="payments-wrap">
    <div class="payments-header">
        <div>
            <h1 style="font-size:32px; margin-bottom:8px;">Pagos</h1>
            <p class="text-muted">Gestiona las transacciones y métodos de pago.</p>
        </div>

        <div class="payments-actions">
            <button type="button" class="btn" id="exportPaymentsButton">Exportar</button>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Completados</div>
            <div class="summary-value" id="sumCompleted">$0</div>
            <div class="summary-note">Hoy</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Pendientes</div>
            <div class="summary-value" id="sumPending">$0</div>
            <div class="summary-note">En proceso</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Transacciones</div>
            <div class="summary-value" id="sumTransactions">0</div>
            <div class="summary-note">En las últimas 24h</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Comisiones</div>
            <div class="summary-value" id="sumCommissions">$0</div>
            <div class="summary-note">Cobradas hoy</div>
        </div>
    </div>

    <div class="filters-card">
        <input type="text" id="paymentsSearch" class="input" placeholder="Buscar por ID, cliente o referencia...">
        <input type="date" id="paymentsDate" class="input">
        <select id="paymentsStatus" class="select">
            <option value="all">Todos los estados</option>
            <option value="COMPLETADO">Completado</option>
            <option value="PENDIENTE">Pendiente</option>
            <option value="CANCELADO">Cancelado</option>
        </select>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">Historial de transacciones</div>
                <div class="card-subtitle" id="paymentsCountText">0 transacciones registradas</div>
            </div>
        </div>

        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID Transacción</th>
                        <th>Fecha y hora</th>
                        <th>Cliente</th>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Método de pago</th>
                        <th>Comisión</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="paymentsTableBody">
                    <tr><td colspan="9" class="empty-box">Cargando pagos...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function money(value) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            maximumFractionDigits: 2
        }).format(Number(value || 0));
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

    function buildStatusBadge(status) {
        if (status === 'PENDIENTE') {
            return '<span class="badge badge-yellow">pendiente</span>';
        }

        if (status === 'CANCELADO') {
            return '<span class="badge badge-red">cancelado</span>';
        }

        return '<span class="badge badge-green">completado</span>';
    }

    function currentFilters() {
        return {
            search: document.getElementById('paymentsSearch').value.trim(),
            date: document.getElementById('paymentsDate').value,
            status: document.getElementById('paymentsStatus').value,
        };
    }

    function buildQueryString() {
        const filters = currentFilters();
        const params = new URLSearchParams();

        params.append('per_page', '50');
        params.append('status', filters.status);

        if (filters.search) params.append('search', filters.search);
        if (filters.date) params.append('date', filters.date);

        return params.toString();
    }

    async function loadSummary() {
        const { response, data } = await apiFetch('/api/payments/summary');

        if (!response.ok) return;

        document.getElementById('sumCompleted').textContent = money(data.completed_today ?? 0);
        document.getElementById('sumPending').textContent = money(data.pending_total ?? 0);
        document.getElementById('sumTransactions').textContent = data.transactions_last_24h ?? 0;
        document.getElementById('sumCommissions').textContent = money(data.commissions_today ?? 0);
    }

    async function loadPayments() {
        const { response, data } = await apiFetch(`/api/payments?${buildQueryString()}`);
        const tbody = document.getElementById('paymentsTableBody');

        if (!response.ok) {
            tbody.innerHTML = `<tr><td colspan="9" class="empty-box">No se pudieron cargar los pagos.</td></tr>`;
            return;
        }

        const items = data.data || [];
        document.getElementById('paymentsCountText').textContent = `${items.length} transacciones registradas`;

        if (!items.length) {
            tbody.innerHTML = `<tr><td colspan="9" class="empty-box">No hay pagos para mostrar.</td></tr>`;
            return;
        }

        tbody.innerHTML = items.map(item => `
            <tr>
                <td style="font-weight:700; color:#1d4ed8;">${item.payment_code}</td>
                <td>${item.date_time}</td>
                <td style="font-weight:600;">${item.customer.name_customer}</td>
                <td>${item.concept}</td>
                <td style="font-weight:700;">${money(item.amount)}</td>
                <td>${item.payment_method}</td>
                <td>${money(item.commission)}</td>
                <td>${buildStatusBadge(item.status)}</td>
                <td>
                    <a href="/pagos/${item.payment_id}" class="icon-btn" title="Ver detalle">👁️</a>
                </td>
            </tr>
        `).join('');
    }

    async function loadPage() {
        await loadSummary();
        await loadPayments();
    }

    function exportPayments() {
        const qs = buildQueryString().replace(/^/, '');
        window.location.href = `/api/payments/export?${qs}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadPage();

        document.getElementById('paymentsSearch').addEventListener('input', loadPayments);
        document.getElementById('paymentsDate').addEventListener('change', loadPayments);
        document.getElementById('paymentsStatus').addEventListener('change', loadPayments);
        document.getElementById('exportPaymentsButton').addEventListener('click', exportPayments);
    });
</script>
@endsection