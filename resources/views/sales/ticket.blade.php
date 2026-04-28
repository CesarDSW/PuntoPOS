@php
    $companyId = auth()->user()->company_idfk;
    $settings = \App\Support\CompanyPreference::settings($companyId);
    $company = \App\Support\CompanyPreference::company($companyId);

    $printerWidth = $settings?->printer_width ?? '80mm';
    $showTaxes = (bool) ($settings?->show_taxes ?? true);
    $autoPrint = request()->boolean('print', false);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $saleId }}</title>

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

        .no-print {
            position: fixed;
            top: 12px;
            right: 12px;
            display: flex;
            gap: 10px;
            z-index: 20;
        }

        .no-print button,
        .no-print a {
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

        .no-print a {
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

        .ticket-center {
            text-align: center;
        }

        .ticket-title {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .width-58 .ticket-title {
            font-size: 16px;
        }

        .ticket-subtitle,
        .ticket-meta,
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

        .loading-box,
        .error-box {
            padding: 18px 12px;
            border-radius: 12px;
            text-align: center;
            margin-top: 40px;
        }

        .loading-box {
            background: #f3f4f6;
        }

        .error-box {
            background: #fee2e2;
            color: #991b1b;
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

            .no-print {
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
    <div class="no-print">
        <a href="{{ route('sales.show', $saleId) }}">Volver</a>
        <button type="button" onclick="window.print()">Imprimir</button>
    </div>

    <div class="ticket-shell {{ $printerWidth === '58mm' ? 'width-58' : 'width-80' }}" id="ticketShell">
        <div class="loading-box" id="ticketLoading">Cargando ticket...</div>
        <div id="ticketContent" style="display:none;"></div>
        <div class="error-box" id="ticketError" style="display:none;"></div>
    </div>

    <script>
        const saleId = {{ (int) $saleId }};
        const ticketConfig = {
            showTaxes: @json($showTaxes),
            autoPrint: @json($autoPrint),
            printerWidth: @json($printerWidth),
            companyName: @json($company->name_company ?? 'Mi negocio'),
            companyPhone: @json($company->phone ?? ''),
            companyAddress: @json($company->address ?? ''),
            companyCurrecy: @json($company->currency ?? 'MXN')
        };

        async function apiFetch(url) {
            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
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

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function renderTicket(sale) {
            const itemsHtml = (sale.items || []).map(item => `
                <div class="ticket-item">
                    <div class="ticket-item-name">${escapeHtml(item.item_name)}</div>
                    <div class="ticket-item-meta">
                        <span>${item.amount} x ${escapeHtml(item.unit_price_display)}</span>
                        <span>${escapeHtml(item.total_line_display)}</span>
                    </div>
                </div>
            `).join('');

            const totalsHtml = ticketConfig.showTaxes
                ? `
                    <div class="row"><span>Subtotal</span><span>${escapeHtml(sale.subtotal_display)}</span></div>
                    ${Number(sale.discount || 0) > 0 ? `<div class="row"><span>Descuento</span><span>- ${escapeHtml(sale.discount_display)}</span></div>` : ''}
                    <div class="row"><span>IVA</span><span>${escapeHtml(sale.iva_display)}</span></div>
                    <div class="row total-main"><span>Total</span><span>${escapeHtml(sale.total_display)}</span></div>
                `
                : `
                    <div class="row total-main"><span>Total</span><span>${escapeHtml(sale.total_display)}</span></div>
                `;

            const paymentReferenceHtml = sale.payment?.reference_payment
                ? `<div class="row"><span>Referencia</span><span>${escapeHtml(sale.payment.reference_payment)}</span></div>`
                : '';

            const paymentCashHtml = `
                <div class="row"><span>Recibido</span><span>${escapeHtml(sale.payment?.amount_paid_display ?? sale.total_display)}</span></div>
                <div class="row"><span>Cambio</span><span>${escapeHtml(sale.payment?.change_given_display ?? sale.total_display)}</span></div>
            `;

            const content = `
                <div class="ticket-center">
                    <div class="ticket-title">${escapeHtml(ticketConfig.companyName)}</div>
                    ${ticketConfig.companyAddress ? `<div class="ticket-subtitle">${escapeHtml(ticketConfig.companyAddress)}</div>` : ''}
                    ${ticketConfig.companyPhone ? `<div class="ticket-subtitle">Tel. ${escapeHtml(ticketConfig.companyPhone)}</div>` : ''}
                    <div class="divider"></div>
                    <div class="strong">TICKET DE VENTA</div>
                    <div class="ticket-meta">Folio: ${escapeHtml(sale.sale_folio)}</div>
                    <div class="ticket-meta">${escapeHtml(sale.date_time_display)}</div>
                    <div class="status-badge">${escapeHtml(sale.status_sale)}</div>
                </div>

                <div class="divider"></div>

                <div class="row"><span>Sucursal</span><span>${escapeHtml(sale.branch?.name_branch ?? '-')}</span></div>
                <div class="row"><span>Cajero</span><span>${escapeHtml(sale.cashier?.name_user ?? '-')}</span></div>
                <div class="row"><span>Cliente</span><span>${escapeHtml(sale.customer?.name_customer ?? 'Cliente general')}</span></div>

                <div class="divider"></div>

                <div class="strong">DETALLE</div>
                <div class="ticket-items">${itemsHtml || '<div class="muted">Sin productos.</div>'}</div>

                <div class="divider"></div>

                <div class="totals">
                    ${totalsHtml}
                </div>

                <div class="divider"></div>

                <div class="strong">PAGO</div>
                <div class="row"><span>Método</span><span>${escapeHtml(sale.payment?.payment_method ?? '-')}</span></div>
                <div class="row"><span>Estado</span><span>${escapeHtml(sale.payment?.status_payment ?? '-')}</span></div>
                ${(sale.payment?.payment_method === 'EFECTIVO' || sale.payment?.payment_method === 'Efectivo') ? paymentCashHtml : paymentReferenceHtml}

                <div class="footer-note">
                    Gracias por su compra<br>
                    Ticket generado por Punto
                </div>
            `;

            const contentBox = document.getElementById('ticketContent');
            document.getElementById('ticketLoading').style.display = 'none';
            contentBox.style.display = 'block';
            contentBox.innerHTML = content;

            if (ticketConfig.autoPrint) {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        }

        async function loadTicket() {
            const { response, data } = await apiFetch(`/api/sales/${saleId}`);

            if (!response.ok) {
                document.getElementById('ticketLoading').style.display = 'none';
                const errorBox = document.getElementById('ticketError');
                errorBox.style.display = 'block';
                errorBox.textContent = data.message || 'No se pudo cargar el ticket.';
                return;
            }

            renderTicket(data);
        }

        document.addEventListener('DOMContentLoaded', loadTicket);
    </script>

    @if($autoPrint)
        <script>
            window.addEventListener('afterprint', function () {
                window.close();
            });
        </script>
    @endif
</body>
</html>