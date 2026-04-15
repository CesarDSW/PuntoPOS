@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/payments/show.css') }}">
@endpush

@section('title', 'Detalle de pago')

@section('content')

<div class="payment-detail-wrap">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
        <div>
            <h1 style="font-size:32px; margin-bottom:8px;">Detalle de pago</h1>
            <p class="text-muted" id="paymentTitleText">Cargando información...</p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="/pagos" class="btn">Volver a pagos</a>
            <a href="/ventas" class="btn">Ir a ventas</a>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Monto</div>
            <div class="summary-value" id="sumAmount">$0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Comisión</div>
            <div class="summary-value" id="sumCommission">$0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Monto recibido</div>
            <div class="summary-value" id="sumPaid">$0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Cambio</div>
            <div class="summary-value" id="sumChange">$0</div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div class="card-title">Información general</div>
            <div class="card-subtitle">Datos de la transacción</div>
        </div>

        <div class="info-grid" id="paymentInfoGrid"></div>
    </div>
</div>

<script>
    const paymentId = @json($paymentId);

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

    async function loadPayment() {
        const { response, data } = await apiFetch(`/api/payments/${paymentId}`);

        if (!response.ok) {
            document.getElementById('paymentTitleText').textContent = 'No se pudo cargar el pago.';
            return;
        }

        document.getElementById('paymentTitleText').innerHTML =
            `${data.payment_code} · ${data.payment_method} · ${buildStatusBadge(data.status)}`;

        document.getElementById('sumAmount').textContent = money(data.sale.total);
        document.getElementById('sumCommission').textContent = money(data.commission);
        document.getElementById('sumPaid').textContent = money(data.amount_paid);
        document.getElementById('sumChange').textContent = money(data.change_given);

        document.getElementById('paymentInfoGrid').innerHTML = `
            <div class="info-box">
                <div class="info-label">Fecha y hora</div>
                <div class="info-value">${data.date_time}</div>
            </div>

            <div class="info-box">
                <div class="info-label">Método de pago</div>
                <div class="info-value">${data.payment_method}</div>
            </div>

            <div class="info-box">
                <div class="info-label">Referencia</div>
                <div class="info-value">${data.reference_payment ?? '-'}</div>
            </div>

            <div class="info-box">
                <div class="info-label">Sucursal</div>
                <div class="info-value">${data.branch.name_branch}</div>
            </div>

            <div class="info-box">
                <div class="info-label">Cliente</div>
                <div class="info-value">${data.customer.name_customer}</div>
            </div>

            <div class="info-box">
                <div class="info-label">Cajero</div>
                <div class="info-value">${data.cashier.name_user}</div>
            </div>

            <div class="info-box">
                <div class="info-label">Venta relacionada</div>
                <div class="info-value">${data.sale.sale_folio}</div>
            </div>

            <div class="info-box">
                <div class="info-label">Estado de la venta</div>
                <div class="info-value">${data.sale.status_sale}</div>
            </div>
        `;
    }

    document.addEventListener('DOMContentLoaded', loadPayment);
</script>
@endsection
