@extends('layouts.app')
@section('title', 'Detalle de caja')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/sales/cash-session-show.css') }}">
@endpush

@section('content')
<div class="cash-detail-wrap">
    <div class="cash-detail-header">
        <div>
            <h1 style="font-size:32px; margin-bottom:8px;">Detalle de caja</h1>
            <p class="text-muted" id="cashTitleText">Cargando información...</p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="/ventas/cajas" class="btn btn-dark">Historial de cajas</a>
            <a href="/ventas" class="btn btn-primary">Volver a ventas</a>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Monto inicial</div>
            <div class="summary-value" id="sumOpening">$0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Monto final</div>
            <div class="summary-value" id="sumClosing">$0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Total vendido</div>
            <div class="summary-value" id="sumSold">$0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Turnos registrados</div>
            <div class="summary-value" id="sumShifts">0</div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div class="card-title">Información general</div>
            <div class="card-subtitle">Datos de apertura y cierre de la caja</div>
        </div>

        <div class="info-grid" id="generalInfoGrid"></div>
    </div>

    <div class="card">
        <div class="card-head">
            <div class="card-title">Resumen por método de pago</div>
            <div class="card-subtitle">Totales de la caja durante toda la sesión</div>
        </div>

        <div class="method-grid" id="paymentMethodsGrid"></div>
    </div>

    <div class="card">
        <div class="card-head">
            <div class="card-title">Turnos dentro de la caja</div>
            <div class="card-subtitle">Resumen operativo de cada turno registrado</div>
        </div>

        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Turno</th>
                        <th>Usuario</th>
                        <th>Sucursal</th>
                        <th>Horario</th>
                        <th>Ventas</th>
                        <th>Total vendido</th>
                        <th>Promedio</th>
                        <th>Pagos</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody id="shiftTableBody">
                    <tr><td colspan="9" class="empty-box">Cargando turnos...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    
</div>

<script>
    const cashSessionId = @json($cashSessionId);

    function money(value) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            maximumFractionDigits: 2
        }).format(Number(value || 0));
    }

    function buildCashCode(id) {
        return 'CJ-' + String(id).padStart(5, '0');
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

    function renderGeneralInfo(data) {
        const statusBadge = data.status_cash === 'ABIERTA'
            ? '<span class="badge badge-green">Abierta</span>'
            : '<span class="badge badge-blue">Cerrada</span>';

        document.getElementById('cashTitleText').innerHTML =
            `${buildCashCode(data.cash_session_id)} · ${data.branch?.name_branch ?? '-'} · ${statusBadge}`;

        document.getElementById('sumOpening').textContent = money(data.opening_amount);
        document.getElementById('sumClosing').textContent = data.closing_amount !== null ? money(data.closing_amount) : '-';
        document.getElementById('sumSold').textContent = money(data.summary?.total_sold ?? 0);
        document.getElementById('sumShifts').textContent = (data.shifts || []).length;

        document.getElementById('generalInfoGrid').innerHTML = `
            <div class="info-box">
                <div class="info-label">Sucursal</div>
                <div class="info-value">${data.branch?.name_branch ?? '-'}</div>
            </div>
            <div class="info-box">
                <div class="info-label">Estado</div>
                <div class="info-value">${data.status_cash}</div>
            </div>
            <div class="info-box">
                <div class="info-label">Abrió caja</div>
                <div class="info-value">${data.opened_by?.name_user ?? '-'}</div>
            </div>
            <div class="info-box">
                <div class="info-label">Cerró caja</div>
                <div class="info-value">${data.closed_by?.name_user ?? '-'}</div>
            </div>
            <div class="info-box">
                <div class="info-label">Fecha de apertura</div>
                <div class="info-value">${data.opened_at ?? '-'}</div>
            </div>
            <div class="info-box">
                <div class="info-label">Fecha de cierre</div>
                <div class="info-value">${data.closed_at ?? '-'}</div>
            </div>
            <div class="info-box">
                <div class="info-label">Observaciones de apertura</div>
                <div class="info-value">${data.notes_opening ?? '-'}</div>
            </div>
            <div class="info-box">
                <div class="info-label">Observaciones de cierre</div>
                <div class="info-value">${data.notes_closing ?? '-'}</div>
            </div>
        `;
    }

    function renderPaymentMethods(data) {
        const methods = data.summary?.payment_methods ?? {};

        document.getElementById('paymentMethodsGrid').innerHTML = `
            <div class="method-box">
                <div class="info-label">Efectivo</div>
                <div class="info-value">${money(methods.EFECTIVO?.total_amount ?? 0)}</div>
                <div style="margin-top:8px; color:#64748b;">${methods.EFECTIVO?.sales_count ?? 0} ventas</div>
            </div>
            <div class="method-box">
                <div class="info-label">Tarjeta</div>
                <div class="info-value">${money(methods.TARJETA?.total_amount ?? 0)}</div>
                <div style="margin-top:8px; color:#64748b;">${methods.TARJETA?.sales_count ?? 0} ventas</div>
            </div>
            <div class="method-box">
                <div class="info-label">Transferencia</div>
                <div class="info-value">${money(methods.TRANSFERENCIA?.total_amount ?? 0)}</div>
                <div style="margin-top:8px; color:#64748b;">${methods.TRANSFERENCIA?.sales_count ?? 0} ventas</div>
            </div>
        `;
    }

    function renderShifts(data) {
        const tbody = document.getElementById('shiftTableBody');
        const shifts = data.shifts || [];

        if (!shifts.length) {
            tbody.innerHTML = `<tr><td colspan="9" class="empty-box">No hay turnos registrados en esta caja.</td></tr>`;
            return;
        }

        tbody.innerHTML = shifts.map(shift => `
            <tr>
                <td style="font-weight:700; color:#1d4ed8;">T-${String(shift.shift_id).padStart(5, '0')}</td>
                <td>${shift.user?.name_user ?? '-'}</td>
                <td>${shift.branch?.name_branch ?? '-'}</td>
                <td>
                    <div><strong>Inicio:</strong> ${shift.started_at ?? '-'}</div>
                    <div><strong>Fin:</strong> ${shift.ended_at ?? '-'}</div>
                </td>
                <td>${shift.sales_count ?? 0}</td>
                <td style="font-weight:700;">${money(shift.total_sold ?? 0)}</td>
                <td>${money(shift.avg_ticket ?? 0)}</td>
                <td>
                    <div>Efe: ${money(shift.payment_methods?.EFECTIVO?.total_amount ?? 0)}</div>
                    <div>Tar: ${money(shift.payment_methods?.TARJETA?.total_amount ?? 0)}</div>
                    <div>Trans: ${money(shift.payment_methods?.TRANSFERENCIA?.total_amount ?? 0)}</div>
                </td>
                <td>${shift.notes_shift ?? '-'}</td>
            </tr>
        `).join('');
    }

    async function loadCashSession() {
        const { response, data } = await apiFetch(`/api/sales/cash/${cashSessionId}`);

        if (!response.ok) {
            document.getElementById('cashTitleText').textContent = 'No se pudo cargar la caja.';
            document.getElementById('shiftTableBody').innerHTML =
                `<tr><td colspan="9" class="empty-box">No se pudo cargar la información.</td></tr>`;
            return;
        }

        renderGeneralInfo(data);
        renderPaymentMethods(data);
        renderShifts(data);
    }

    document.addEventListener('DOMContentLoaded', loadCashSession);
</script>
@endsection