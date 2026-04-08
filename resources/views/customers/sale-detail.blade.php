@extends('layout.dashboard_design')

@section('content')
<style>
    .customer-sale-page {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .page-header h1 {
        margin: 0 0 8px;
        font-size: 32px;
        color: #0f172a;
    }

    .page-header p {
        margin: 0;
        color: #64748b;
        font-size: 16px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 48px;
        padding: 0 20px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        background: #fff;
        color: #0f172a;
        text-decoration: none;
        font-weight: 600;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .summary-card,
    .card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
    }

    .summary-card {
        padding: 20px;
    }

    .summary-label {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .summary-value {
        font-size: 22px;
        font-weight: 700;
        color: #0f172a;
    }

    .summary-note {
        margin-top: 8px;
        color: #64748b;
        font-size: 13px;
    }

    .card-head {
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-title {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .card-subtitle {
        color: #64748b;
        font-size: 14px;
        margin-top: 4px;
    }

    .sale-meta-grid {
        padding: 20px;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .sale-meta-block strong {
        display: block;
        font-size: 13px;
        color: #64748b;
        margin-bottom: 6px;
    }

    .sale-meta-block span {
        color: #0f172a;
        font-weight: 600;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        font-size: 14px;
        vertical-align: middle;
    }

    th {
        background: #f8fafc;
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
    }

    .totals-box {
        padding: 20px;
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 20px;
    }

    .totals-panel {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px;
        background: #f8fafc;
    }

    .totals-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
        color: #0f172a;
    }

    .totals-row:last-child {
        margin-bottom: 0;
    }

    .totals-row.total {
        font-size: 22px;
        font-weight: 700;
        padding-top: 12px;
        border-top: 1px solid #d1d5db;
    }

    .payment-panel {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px;
        background: #fff;
    }

    .payment-panel h3 {
        margin: 0 0 14px;
        font-size: 16px;
        color: #0f172a;
    }

    .payment-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
        color: #0f172a;
    }

    .payment-row:last-child {
        margin-bottom: 0;
    }

    .badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
    }

    .badge-blue { background: #eef2ff; color: #1d4ed8; }
    .badge-yellow { background: #fef3c7; color: #92400e; }
    .badge-red { background: #fee2e2; color: #b91c1c; }

    .empty-box {
        padding: 22px;
        text-align: center;
        color: #64748b;
    }

    @media (max-width: 1200px) {
        .summary-grid,
        .sale-meta-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .totals-box {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .summary-grid,
        .sale-meta-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@php
    $badge = 'badge-blue';
    if ($sale->status_sale === 'PENDIENTE') {
        $badge = 'badge-yellow';
    } elseif ($sale->status_sale === 'CANCELADA') {
        $badge = 'badge-red';
    }
@endphp

<div class="customer-sale-page">
    <div class="page-header">
        <div>
            <h1>Detalle de venta</h1>
            <p>Venta relacionada con {{ $customer->name_customer }}.</p>
        </div>

        <a href="{{ route('customers.history', $customer->customer_id) }}" class="btn-back">Volver al historial</a>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Folio</div>
            <div class="summary-value">V-{{ str_pad($sale->sale_id, 4, '0', STR_PAD_LEFT) }}</div>
            <div class="summary-note">Identificador de la venta</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Total</div>
            <div class="summary-value">${{ number_format((float) $sale->total, 2) }}</div>
            <div class="summary-note">Monto final cobrado</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Método de pago</div>
            <div class="summary-value" style="font-size:18px;">{{ $sale->payment_methods }}</div>
            <div class="summary-note">Forma de pago registrada</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Estado</div>
            <div class="summary-value"><span class="badge {{ $badge }}">{{ $sale->status_sale }}</span></div>
            <div class="summary-note">{{ \Carbon\Carbon::parse($sale->date_time)->format('Y-m-d H:i') }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div class="card-title">Datos de la venta</div>
            <div class="card-subtitle">Información general de esta compra</div>
        </div>

        <div class="sale-meta-grid">
            <div class="sale-meta-block">
                <strong>Cliente</strong>
                <span>{{ $customer->name_customer }}</span>
            </div>

            <div class="sale-meta-block">
                <strong>Cajero</strong>
                <span>{{ $sale->cashier_name }}</span>
            </div>

            <div class="sale-meta-block">
                <strong>Sucursal</strong>
                <span>{{ $sale->name_branch }}</span>
            </div>

            <div class="sale-meta-block">
                <strong>Negocio</strong>
                <span>{{ $sale->name_company }}</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div class="card-title">Conceptos</div>
            <div class="card-subtitle">{{ $items->count() }} elementos en la venta</div>
        </div>

        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Precio unitario</th>
                        <th>Descuento</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->item_name ?? '-' }}</td>
                            <td>{{ $item->item_type }}</td>
                            <td>{{ $item->amount }}</td>
                            <td>${{ number_format((float) $item->unit_price, 2) }}</td>
                            <td>${{ number_format((float) $item->discount, 2) }}</td>
                            <td style="font-weight:700;">${{ number_format((float) $item->total_line, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-box">No hay conceptos registrados en esta venta.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="totals-box">
        <div class="totals-panel">
            <div class="totals-row">
                <span>Subtotal</span>
                <strong>${{ number_format((float) $sale->subtotal, 2) }}</strong>
            </div>

            <div class="totals-row">
                <span>Descuento</span>
                <strong>${{ number_format((float) $sale->discount, 2) }}</strong>
            </div>

            <div class="totals-row total">
                <span>Total</span>
                <strong>${{ number_format((float) $sale->total, 2) }}</strong>
            </div>
        </div>

        <div class="payment-panel">
            <h3>Pago</h3>

            <div class="payment-row">
                <span>Método</span>
                <strong>{{ $sale->payment_methods }}</strong>
            </div>

            <div class="payment-row">
                <span>Recibido</span>
                <strong>${{ number_format((float) $sale->amount_paid, 2) }}</strong>
            </div>

            <div class="payment-row">
                <span>Cambio</span>
                <strong>${{ number_format((float) $sale->change_given, 2) }}</strong>
            </div>

            <div class="payment-row">
                <span>Referencia</span>
                <strong>{{ $sale->reference_payment ?: '-' }}</strong>
            </div>
        </div>
    </div>
</div>
@endsection