@extends('layout.dashboard_design')

@section('content')
<style>
    .customer-history-page {
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

    .customer-info {
        padding: 20px;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 16px;
    }

    .customer-info-block strong {
        display: block;
        font-size: 13px;
        color: #64748b;
        margin-bottom: 6px;
    }

    .customer-info-block span {
        color: #0f172a;
        font-weight: 600;
    }

    .card-head {
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
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

    .badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
    }

    .badge-blue { background: #eef2ff; color: #1d4ed8; }
    .badge-yellow { background: #fef3c7; color: #92400e; }
    .badge-red { background: #fee2e2; color: #b91c1c; }

    .icon-link {
        color: #1d4ed8;
        text-decoration: none;
        font-size: 18px;
    }

    .empty-box {
        padding: 22px;
        text-align: center;
        color: #64748b;
    }

    @media (max-width: 1100px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .customer-info {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

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