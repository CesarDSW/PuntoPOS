@extends('layouts.app')

@section('title', 'Historial de cajas')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/sales/cash-history.css') }}">
@endpush

@section('content')

<div class="cash-wrap">
    <div class="cash-header">
        <div>
            <h1 style="font-size:32px; margin-bottom:8px;">Historial de cajas</h1>
            <p class="text-muted">Consulta aperturas, cierres y turnos de cada caja.</p>
        </div>

        <div class="cash-actions">
            <a href="/ventas" class="btn btn-dark">Volver a ventas</a>
        </div>
    </div>

    <div class="filters-card">
        <input type="text" id="cashSearch" class="input" placeholder="Buscar por folio, sucursal o usuario...">
        <select id="cashStatus" class="select">
            <option value="all">Todas</option>
            <option value="ABIERTA">Abiertas</option>
            <option value="CERRADA">Cerradas</option>
        </select>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Sesiones registradas</div>
            <div class="summary-value" id="sumSessions">0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Cajas abiertas</div>
            <div class="summary-value" id="sumOpen">0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Cajas cerradas</div>
            <div class="summary-value" id="sumClosed">0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Total vendido</div>
            <div class="summary-value" id="sumSold">$0</div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">Sesiones de caja</div>
                <div class="card-subtitle" id="cashCountText">0 registros</div>
            </div>
        </div>

        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Caja</th>
                        <th>Sucursal</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Abrió / Cerró</th>
                        <th>Montos</th>
                        <th>Turnos</th>
                        <th>Ventas</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="cashTableBody">
                    <tr><td colspan="10" class="empty-box">Cargando historial...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let allSessions = [];

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

    function buildCashCode(id) {
        return 'CJ-' + String(id).padStart(5, '0');
    }

    function renderSummary(items) {
        const openCount = items.filter(x => x.status_cash === 'ABIERTA').length;
        const closedCount = items.filter(x => x.status_cash === 'CERRADA').length;
        const totalSold = items.reduce((sum, item) => sum + Number(item.total_sold || 0), 0);

        document.getElementById('sumSessions').textContent = items.length;
        document.getElementById('sumOpen').textContent = openCount;
        document.getElementById('sumClosed').textContent = closedCount;
        document.getElementById('sumSold').textContent = money(totalSold);
    }

    function renderTable(items) {
        const tbody = document.getElementById('cashTableBody');
        document.getElementById('cashCountText').textContent = `${items.length} registros`;

        if (!items.length) {
            tbody.innerHTML = `<tr><td colspan="10" class="empty-box">No hay sesiones de caja para mostrar.</td></tr>`;
            return;
        }

        tbody.innerHTML = items.map(item => {
            const statusBadge = item.status_cash === 'ABIERTA'
                ? '<span class="badge badge-green">Abierta</span>'
                : '<span class="badge badge-blue">Cerrada</span>';

            return `
                <tr>
                    <td style="font-weight:700; color:#1d4ed8;">${buildCashCode(item.cash_session_id)}</td>
                    <td>${item.branch?.name_branch ?? '-'}</td>
                    <td>${item.opened_at ?? '-'}</td>
                    <td>${item.closed_at ?? '-'}</td>
                    <td>
                        <div><strong>Abrió:</strong> ${item.opened_by_name ?? '-'}</div>
                        <div><strong>Cerró:</strong> ${item.closed_by_name ?? '-'}</div>
                    </td>
                    <td>
                        <div><strong>Inicial:</strong> ${money(item.opening_amount)}</div>
                        <div><strong>Final:</strong> ${item.closing_amount !== null ? money(item.closing_amount) : '-'}</div>
                    </td>
                    <td>${item.shifts_count} turnos<br><span style="color:#64748b;">${item.cashiers_count} usuarios</span></td>
                    <td>
                        <div><strong>${money(item.total_sold)}</strong></div>
                        <div style="color:#64748b;">${item.sales_count} ventas</div>
                    </td>
                    <td>${statusBadge}</td>
                    <td><a class="link-btn" href="/ventas/cajas/${item.cash_session_id}">Ver detalle</a></td>
                </tr>
            `;
        }).join('');
    }

    function applyFilters() {
        const search = document.getElementById('cashSearch').value.trim().toLowerCase();
        const status = document.getElementById('cashStatus').value;

        let items = [...allSessions];

        if (status !== 'all') {
            items = items.filter(item => item.status_cash === status);
        }

        if (search !== '') {
            items = items.filter(item => {
                const joined = [
                    item.cash_session_id,
                    item.branch?.name_branch,
                    item.opened_by_name,
                    item.closed_by_name
                ].join(' ').toLowerCase();

                return joined.includes(search);
            });
        }

        renderSummary(items);
        renderTable(items);
    }

    async function loadHistory() {
        const { response, data } = await apiFetch('/api/sales/cash/history');
        if (!response.ok) {
            document.getElementById('cashTableBody').innerHTML =
                `<tr><td colspan="10" class="empty-box">No se pudo cargar el historial.</td></tr>`;
            return;
        }

        allSessions = Array.isArray(data) ? data : [];
        applyFilters();
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadHistory();
        document.getElementById('cashSearch').addEventListener('input', applyFilters);
        document.getElementById('cashStatus').addEventListener('change', applyFilters);
    });
</script>
@endsection