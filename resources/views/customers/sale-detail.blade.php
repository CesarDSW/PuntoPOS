@extends('layout.dashboard_design')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/customers/sale-detail.css') }}">
@endpush

@section('content')

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
            <div class="summary-value">
                <span class="badge {{ $badge }}">{{ $sale->status_sale }}</span>
            </div>
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
            <table class="sale-detail-table">
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
                            <td class="sale-price">${{ number_format((float) $item->unit_price, 2) }}</td>
                            <td>${{ number_format((float) $item->discount, 2) }}</td>
                            <td class="sale-total">${{ number_format((float) $item->total_line, 2) }}</td>
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