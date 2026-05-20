@php
    use Illuminate\Support\Facades\Storage;

    $companyId = auth()->user()->company_idfk ?? null;
    $company = \App\Support\CompanyPreference::company($companyId);
    $settings = \App\Support\CompanyPreference::settings($companyId);

    $companyName = $company->name_company ?? 'Punto';
    $companyAddress = $company->address ?? '';
    $companyPhone = $company->phone ?? '';
    $companyEmail = $company->email ?? '';
    $companyRfc = $company->rfc ?? '';

    $logoUrl = null;

    if (!empty($company?->logo) && Storage::disk('public')->exists($company->logo)) {
        $logoUrl = asset('storage/' . ltrim($company->logo, '/'));
    }
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #{{ $saleId }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #f3f6fb;
            color: #0f172a;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            padding: 24px;
        }

        .no-print {
            max-width: 980px;
            margin: 0 auto 14px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .no-print a,
        .no-print button {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            background: #0b79c8;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }

        .no-print a {
            background: #334155;
        }

        .invoice-page {
            width: 980px;
            min-height: 1260px;
            margin: 0 auto;
            background: #fff;
            padding: 28px 32px 24px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, .14);
        }

        .invoice-title {
            color: #5b9bd5;
            font-size: 38px;
            font-weight: 800;
            letter-spacing: .5px;
            margin: 0 0 26px;
            text-transform: uppercase;
        }

        .top-grid {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 32px;
            align-items: start;
        }

        .brand-row {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 18px;
        }

        .company-logo {
            width: 190px;
            max-height: 95px;
            object-fit: contain;
        }

        .logo-fallback {
            color: #f2b705;
            font-size: 38px;
            font-weight: 800;
            line-height: 1;
            padding: 12px 0;
        }

        .company-info {
            color: #0066c9;
            line-height: 1.65;
            font-size: 16px;
        }

        .invoice-label {
            color: #0066c9;
            text-align: right;
            font-size: 34px;
            font-weight: 500;
            margin: 18px 0 30px;
            text-transform: uppercase;
        }

        .mini-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
            text-align: center;
        }

        .mini-table th {
            background: #0070c0;
            color: #fff;
            padding: 7px 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .mini-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #d7e3f7;
        }

        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 64px;
            margin-top: 24px;
        }

        .block-title {
            background: #0070c0;
            color: #fff;
            padding: 6px 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .address-box {
            line-height: 1.55;
            font-size: 15px;
            padding: 6px 2px 0;
            min-height: 124px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 22px;
            font-size: 15px;
        }

        .items-table th {
            background: #0070c0;
            color: #fff;
            padding: 8px 10px;
            text-transform: uppercase;
            border-right: 1px solid #94b8e8;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            border-right: 0;
        }

        .items-table td {
            height: 28px;
            border: 1px solid #b8c9ee;
            padding: 6px 8px;
        }

        .items-table .amount {
            text-align: right;
            white-space: nowrap;
        }

        .bottom-grid {
            display: grid;
            grid-template-columns: 1.25fr .75fr;
            gap: 22px;
            margin-top: 0;
        }

        .notes-box {
            min-height: 155px;
            padding: 8px 2px;
            font-size: 14px;
        }

        .thanks {
            text-align: center;
            color: #0066c9;
            font-size: 24px;
            margin-top: 55px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }

        .totals-table td {
            background: #dbe5f6;
            border: 1px solid #b8c9ee;
            padding: 10px 12px;
        }

        .totals-table td:last-child {
            text-align: right;
            white-space: nowrap;
        }

        .totals-table .grand td {
            font-weight: 800;
        }

        .footer {
            margin-top: 36px;
            text-align: center;
            color: #0066c9;
            line-height: 1.5;
            font-size: 15px;
        }

        .footer-line {
            margin-top: 22px;
            border-top: 3px solid #0070c0;
        }

        .loading,
        .error {
            text-align: center;
            padding: 38px 20px;
            color: #64748b;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            margin-top: 20px;
        }

        .error {
            color: #991b1b;
            background: #fee2e2;
            border-color: #fecaca;
        }

        @media print {
            html, body {
                background: #fff;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .invoice-page {
                width: 100%;
                min-height: auto;
                margin: 0;
                box-shadow: none;
                padding: 18mm 14mm;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="{{ route('sales.index') }}">Volver</a>
        <a href="{{ route('sales.ticket', $saleId) }}" target="_blank">Ver ticket</a>
        <button type="button" onclick="window.print()">Imprimir factura</button>
    </div>

    <div class="invoice-page">
        <h1 class="invoice-title">Factura</h1>

        <div id="invoiceLoading" class="loading">Cargando factura...</div>
        <div id="invoiceError" class="error" style="display:none;"></div>
        <div id="invoiceContent" style="display:none;"></div>
    </div>

    <script>
        const saleId = @json($saleId);
        const invoiceCompany = {
            name: @json($companyName),
            address: @json($companyAddress),
            phone: @json($companyPhone),
            email: @json($companyEmail),
            rfc: @json($companyRfc),
            logoUrl: @json($logoUrl)
        };

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

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function safe(value, fallback = '-') {
            return value === null || value === undefined || value === '' ? fallback : value;
        }

        function renderBlankRows(currentCount, minRows = 12) {
            const missing = Math.max(minRows - currentCount, 0);
            return Array.from({ length: missing }).map(() => `
                <tr>
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            `).join('');
        }

        function renderInvoice(sale) {
            const items = sale.items || [];
            const logoHtml = invoiceCompany.logoUrl
                ? `<img class="company-logo" src="${escapeHtml(invoiceCompany.logoUrl)}" alt="Logo">`
                : `<div class="logo-fallback">${escapeHtml(invoiceCompany.name)}</div>`;

            const companyInfo = [
                invoiceCompany.name,
                invoiceCompany.rfc ? `RFC: ${invoiceCompany.rfc}` : '',
                invoiceCompany.address,
                invoiceCompany.phone ? `Tel. ${invoiceCompany.phone}` : '',
                invoiceCompany.email
            ].filter(Boolean).map(escapeHtml).join('<br>');

            const customer = sale.customer || {};
            const payment = sale.payment || {};
            const invoiceDate = sale.date_time_display || safe(sale.date_time);
            const invoiceNumber = String(sale.sale_id || saleId).padStart(5, '0');

            const itemsHtml = items.map(item => `
                <tr>
                    <td>${escapeHtml(item.amount)}</td>
                    <td>${escapeHtml(item.item_name)}</td>
                    <td class="amount">${escapeHtml(item.unit_price_display ?? item.unit_price)}</td>
                    <td class="amount">${escapeHtml(item.total_line_display ?? item.total_line)}</td>
                </tr>
            `).join('');

            document.getElementById('invoiceContent').innerHTML = `
                <div class="top-grid">
                    <div>
                        <div class="brand-row">
                            ${logoHtml}
                        </div>
                        <div class="company-info">${companyInfo || 'Datos de la empresa'}</div>
                    </div>

                    <div>
                        <div class="invoice-label">Factura</div>
                        <table class="mini-table">
                            <tr>
                                <th>N.º de factura</th>
                                <th>Fecha</th>
                            </tr>
                            <tr>
                                <td>${escapeHtml(invoiceNumber)}</td>
                                <td>${escapeHtml(invoiceDate)}</td>
                            </tr>
                            <tr>
                                <th>ID de cliente</th>
                                <th>Condiciones</th>
                            </tr>
                            <tr>
                                <td>${escapeHtml(customer.customer_id ?? 'Cliente general')}</td>
                                <td>${escapeHtml(payment.payment_method ?? '-')}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="address-grid">
                    <div>
                        <div class="block-title">Facturar a:</div>
                        <div class="address-box">
                            <strong>${escapeHtml(customer.name_customer ?? 'Cliente general')}</strong><br>
                            ${customer.phone ? `Tel. ${escapeHtml(customer.phone)}<br>` : ''}
                            ${customer.email ? `${escapeHtml(customer.email)}<br>` : ''}
                            ${escapeHtml('Sucursal: ' + safe(sale.branch?.name_branch))}
                        </div>
                    </div>

                    <div>
                        <div class="block-title">Enviar a:</div>
                        <div class="address-box">
                            <strong>${escapeHtml(customer.name_customer ?? 'Cliente general')}</strong><br>
                            ${customer.phone ? `Tel. ${escapeHtml(customer.phone)}<br>` : ''}
                            ${customer.email ? `${escapeHtml(customer.email)}<br>` : ''}
                            ${escapeHtml('Atendió: ' + safe(sale.cashier?.name_user))}
                        </div>
                    </div>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width:90px;">Cant.</th>
                            <th>Descripción</th>
                            <th style="width:150px;">Precio</th>
                            <th style="width:160px;">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                        ${renderBlankRows(items.length)}
                    </tbody>
                </table>

                <div class="bottom-grid">
                    <div class="notes-box">
                        <strong>Observaciones/Instrucciones:</strong><br>
                        Folio de venta: ${escapeHtml(sale.sale_folio)}<br>
                        Estado: ${escapeHtml(sale.status_sale)}<br>
                        ${payment.reference_payment ? `Referencia: ${escapeHtml(payment.reference_payment)}` : ''}
                        <div class="thanks">GRACIAS</div>
                    </div>

                    <table class="totals-table">
                        <tr><td>SUBTOTAL</td><td>${escapeHtml(sale.subtotal_display)}</td></tr>
                        <tr><td>IMPUESTOS</td><td>${escapeHtml(sale.iva_display)}</td></tr>
                        <tr><td>DESCUENTO</td><td>${escapeHtml(sale.discount_display)}</td></tr>
                        <tr class="grand"><td>TOTAL</td><td>${escapeHtml(sale.total_display)}</td></tr>
                    </table>
                </div>

                <div class="footer">
                    Si tienes preguntas relacionadas con esta factura, ponte en contacto con<br>
                    ${escapeHtml(invoiceCompany.name)}${invoiceCompany.phone ? `, ${escapeHtml(invoiceCompany.phone)}` : ''}${invoiceCompany.email ? `, ${escapeHtml(invoiceCompany.email)}` : ''}
                </div>
                <div class="footer-line"></div>
            `;

            document.getElementById('invoiceLoading').style.display = 'none';
            document.getElementById('invoiceContent').style.display = 'block';
        }

        async function loadInvoice() {
            const { response, data } = await apiFetch(`/api/sales/${saleId}`);

            if (!response.ok) {
                document.getElementById('invoiceLoading').style.display = 'none';
                const errorBox = document.getElementById('invoiceError');
                errorBox.style.display = 'block';
                errorBox.textContent = data.message || 'No se pudo cargar la factura.';
                return;
            }

            renderInvoice(data);
        }

        document.addEventListener('DOMContentLoaded', loadInvoice);
    </script>
</body>
</html>