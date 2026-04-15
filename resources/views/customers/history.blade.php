@extends('layout.dashboard_design')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/customers/history.css') }}">
@endpush

@section('content')

<div class="customer-history-page">
    <div class="page-header">
        <div>
            <h1>Historial del cliente</h1>
            <p>Consulta las compras realizadas por {{ $customer->name_customer }}.</p>
        </div>

        <a href="{{ route('customers') }}" class="btn-back">Volver</a>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Total gastado</div>
            <div class="summary-value">${{ number_format($totalSpent, 2) }}</div>
            <div class="summary-note">Compras acumuladas</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Compras realizadas</div>
            <div class="summary-value">{{ $purchasesCount }}</div>
            <div class="summary-note">Ventas registradas</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Ticket promedio</div>
            <div class="summary-value">${{ number_format($avgTicket, 2) }}</div>
            <div class="summary-note">Promedio por compra</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Última compra</div>
            <div class="summary-value">
                {{ $lastPurchaseAt ? \Carbon\Carbon::parse($lastPurchaseAt)->format('Y-m-d') : '-' }}
            </div>
            <div class="summary-note">Fecha más reciente</div>
        </div>
    </div>

    <div class="card">
        <div class="customer-info">
            <div class="customer-info-block">
                <strong>Cliente</strong>
                <span>{{ $customer->name_customer }}</span>
            </div>

            <div class="customer-info-block">
                <strong>Email</strong>
                <span>{{ $customer->email }}</span>
            </div>

            <div class="customer-info-block">
                <strong>Teléfono</strong>
                <span>{{ $customer->phone }}</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">Compras del cliente</div>
                <div class="card-subtitle">{{ $sales->count() }} registros encontrados</div>
            </div>
        </div>

        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID Venta</th>
                        <th>Fecha y hora</th>
                        <th>Total</th>
                        <th>Método de pago</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        @php
                            $badge = 'badge-blue';
                            $statusText = $sale->status_sale;

                            if ($sale->status_sale === 'PENDIENTE') {
                                $badge = 'badge-yellow';
                            } elseif ($sale->status_sale === 'CANCELADA') {
                                $badge = 'badge-red';
                            }
                        @endphp

                        <tr>
                            <td style="font-weight:700; color:#1d4ed8;">
                                V-{{ str_pad($sale->sale_id, 4, '0', STR_PAD_LEFT) }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($sale->date_time)->format('Y-m-d H:i') }}</td>
                            <td style="font-weight:700;">${{ number_format((float) $sale->total, 2) }}</td>
                            <td>{{ $sale->payment_methods }}</td>
                            <td><span class="badge {{ $badge }}">{{ $statusText }}</span></td>
                            <td>
                                <a href="{{ route('customers.sales.show', [$customer->customer_id, $sale->sale_id]) }}" class="icon-link" title="Ver detalle">👁️</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-box">Este cliente todavía no tiene compras registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection