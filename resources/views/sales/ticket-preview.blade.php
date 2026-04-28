@php
    $companyId = auth()->user()->company_idfk;
    $settings = \App\Support\CompanyPreference::settings($companyId);
    $company = \App\Support\CompanyPreference::company($companyId);

    $printerWidth = request('width', $settings?->printer_width ?? '80mm');
    $showTaxes = request()->has('taxes')
        ? request()->boolean('taxes')
        : (bool) ($settings?->show_taxes ?? true);

    $autoPrint = request()->boolean('print', false);

    $mockItems = [
        [
            'item_name' => 'Coca Cola 600ml',
            'amount' => 2,
            'unit_price' => 18.50,
        ],
        [
            'item_name' => 'Sabritas Original',
            'amount' => 1,
            'unit_price' => 21.00,
        ],
        [
            'item_name' => 'Galletas Marías',
            'amount' => 3,
            'unit_price' => 12.00,
        ],
    ];

    $subtotal = collect($mockItems)->sum(function ($item) {
        return $item['amount'] * $item['unit_price'];
    });

    $iva = $subtotal * 0.16;
    $total = $subtotal + $iva;

    $sale = [
        'sale_folio' => 'TST-' . now()->format('Ymd-His'),
        'date_time_display' => \App\Support\CompanyPreference::formatDateTimeForCompany($companyId, now()),
        'status_sale' => 'PAGADA',
        'branch' => [
            'name_branch' => 'Sucursal principal',
        ],
        'cashier' => [
            'name_user' => auth()->user()->name_user ?? 'Cajero prueba',
        ],
        'customer' => [
            'name_customer' => 'Cliente general',
        ],
        'payment' => [
            'payment_method' => 'EFECTIVO',
            'status_payment' => 'COMPLETADO',
            'reference_payment' => null,
            'amount_paid_display' => \App\Support\CompanyPreference::formatMoneyForCompany($companyId, 200),
            'change_given_display' => \App\Support\CompanyPreference::formatMoneyForCompany($companyId, 200 - $total),
        ],
        'items' => collect($mockItems)->map(function ($item) use ($companyId) {
            $line = $item['amount'] * $item['unit_price'];

            return [
                'item_name' => $item['item_name'],
                'amount' => $item['amount'],
                'unit_price_display' => \App\Support\CompanyPreference::formatMoneyForCompany($companyId, $item['unit_price']),
                'total_line_display' => \App\Support\CompanyPreference::formatMoneyForCompany($companyId, $line),
            ];
        })->values()->all(),
        'subtotal_display' => \App\Support\CompanyPreference::formatMoneyForCompany($companyId, $subtotal),
        'iva_display' => \App\Support\CompanyPreference::formatMoneyForCompany($companyId, $iva),
        'total_display' => \App\Support\CompanyPreference::formatMoneyForCompany($companyId, $total),
        'discount_display' => \App\Support\CompanyPreference::formatMoneyForCompany($companyId, 0),
        'discount' => 0,
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de prueba</title>

    <style>
        @page {
            size: {{ $printerWidth }} auto;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
        }

        body {
            display: flex;
            justify-content: center;
            padding: 16px;
        }

        .controls {
            position: fixed;
            top: 12px;
            right: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            z-index: 20;
        }

        .controls a,
        .controls button {
            border: none;
            background: #1d4ed8;
            color: #fff;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
        }

        .controls a.alt {
            background: #374151;
        }

        .ticket-shell {
            background: #fff;
            color: #000;
            width: 80mm;
            min-height: 100vh;
            padding: 10px 8px 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .ticket-shell.width-58 {
            width: 58mm;
            padding: 8px 6px 12px;
            font-size: 12px;
        }

        .ticket-shell.width-80 {
            width: 80mm;
            font-size: 13px;
        }

        .center {
            text-align: center;
        }

        .logo-wrap {
            margin-bottom: 8px;
        }

        .logo-wrap img {
            max-width: 110px;
            max-height: 70px;
            object-fit: contain;
        }

        .title {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .width-58 .title {
            font-size: 16px;
        }

        .muted {
            color: #4b5563;
            line-height: 1.35;
        }

        .divider {
            border-top: 1px dashed #9ca3af;
            margin: 10px 0;
        }

        .strong {
            font-weight: 700;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 4px;
        }

        .row span:last-child {
            text-align: right;
            white-space: nowrap;
        }

        .ticket-items {
            margin-top: 8px;
        }

        .ticket-item {
            padding: 6px 0;
            border-bottom: 1px dashed #d1d5db;
        }

        .ticket-item:last-child {
            border-bottom: none;
        }

        .ticket-item-name {
            font-weight: 700;
            margin-bottom: 2px;
            word-break: break-word;
        }

        .ticket-item-meta {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            color: #374151;
        }

        .totals {
            margin-top: 8px;
        }

        .total-main {
            font-size: 16px;
            font-weight: 800;
            margin-top: 6px;
        }

        .width-58 .total-main {
            font-size: 14px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid #111827;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            margin-top: 4px;
        }

        .footer-note {
            margin-top: 12px;
            text-align: center;
            font-size: 11px;
            color: #4b5563;
            line-height: 1.4;
        }

        @media print {
            html, body {
                background: #fff;
                padding: 0;
            }

            .controls {
                display: none !important;
            }

            .ticket-shell {
                box-shadow: none;
                min-height: auto;
                width: 100%;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="controls">
        <a href="{{ route('sales.ticket.preview', ['width' => '58mm', 'taxes' => $showTaxes ? 1 : 0]) }}" class="alt">Ver 58mm</a>
        <a href="{{ route('sales.ticket.preview', ['width' => '80mm', 'taxes' => $showTaxes ? 1 : 0]) }}" class="alt">Ver 80mm</a>
        <a href="{{ route('sales.ticket.preview', ['width' => $printerWidth, 'taxes' => $showTaxes ? 0 : 1]) }}" class="alt">
            {{ $showTaxes ? 'Ocultar impuestos' : 'Mostrar impuestos' }}
        </a>
        <button type="button" onclick="window.print()">Imprimir</button>
    </div>

    <div class="ticket-shell {{ $printerWidth === '58mm' ? 'width-58' : 'width-80' }}">
        <div class="center">
            @if(!empty($company?->logo))
                <div class="logo-wrap">
                    <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo">
                </div>
            @endif

            <div class="title">{{ $company->name_company ?? 'Mi negocio' }}</div>

            @if(!empty($company?->address))
                <div class="muted">{{ $company->address }}</div>
            @endif

            @if(!empty($company?->phone))
                <div class="muted">Tel. {{ $company->phone }}</div>
            @endif

            <div class="divider"></div>

            <div class="strong">TICKET DE PRUEBA</div>
            <div class="muted">Folio: {{ $sale['sale_folio'] }}</div>
            <div class="muted">{{ $sale['date_time_display'] }}</div>
            <div class="status-badge">{{ $sale['status_sale'] }}</div>
        </div>

        <div class="divider"></div>

        <div class="row"><span>Sucursal</span><span>{{ $sale['branch']['name_branch'] }}</span></div>
        <div class="row"><span>Cajero</span><span>{{ $sale['cashier']['name_user'] }}</span></div>
        <div class="row"><span>Cliente</span><span>{{ $sale['customer']['name_customer'] }}</span></div>

        <div class="divider"></div>

        <div class="strong">DETALLE</div>

        <div class="ticket-items">
            @foreach($sale['items'] as $item)
                <div class="ticket-item">
                    <div class="ticket-item-name">{{ $item['item_name'] }}</div>
                    <div class="ticket-item-meta">
                        <span>{{ $item['amount'] }} x {{ $item['unit_price_display'] }}</span>
                        <span>{{ $item['total_line_display'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <div class="totals">
            @if($showTaxes)
                <div class="row"><span>Subtotal</span><span>{{ $sale['subtotal_display'] }}</span></div>
                @if((float) ($sale['discount'] ?? 0) > 0)
                    <div class="row"><span>Descuento</span><span>- {{ $sale['discount_display'] }}</span></div>
                @endif
                <div class="row"><span>IVA</span><span>{{ $sale['iva_display'] }}</span></div>
            @endif
            
            <div class="row total-main">
                <span>Total</span>
                <span>{{ $sale['total_display'] }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="strong">PAGO</div>
        <div class="row"><span>Método</span><span>{{ $sale['payment']['payment_method'] }}</span></div>
        <div class="row"><span>Estado</span><span>{{ $sale['payment']['status_payment'] }}</span></div>
        <div class="row"><span>Recibido</span><span>{{ $sale['payment']['amount_paid_display'] }}</span></div>
        <div class="row"><span>Cambio</span><span>{{ $sale['payment']['change_given_display'] }}</span></div>

        <div class="footer-note">
            Gracias por su compra<br>
            Ticket generado por Punto
        </div>
    </div>

    @if($autoPrint)
        <script>
            window.addEventListener('load', function () {
                setTimeout(() => {
                    window.print();
                }, 400);
            });
        </script>
    @endif
</body>
</html>