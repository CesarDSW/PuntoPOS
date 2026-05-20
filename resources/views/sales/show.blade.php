@php 
    $salesShowAccess = [
        'view' => \App\Support\UserAccess::has(auth()->user(), 'sales.view'),
        'create' => \App\Support\UserAccess::has(auth()->user(), 'sales.create'),
        'ticket_print' => \App\Support\UserAccess::has(auth()->user(), 'sales.ticket.print'),
    ];
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de venta</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f6f7fb;
            color: #0f172a;
        }

        .wrap {
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .top-actions {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 14px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .btn {
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid #d1d5db;
            background: #fff;
            cursor: pointer;
            text-decoration: none;
            color: #0f172a;
            font-weight: 600;
        }

        .btn-primary {
            background: #1d4ed8;
            color: #fff;
            border-color: #1d4ed8;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 22px;
        }

        .card h2 {
            font-size: 22px;
            margin-bottom: 14px;
        }

        .card h3 {
            font-size: 18px;
            margin-bottom: 12px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .row:last-child {
            border-bottom: none;
        }

        .label {
            color: #64748b;
            font-weight: 600;
        }

        .value {
            font-weight: 700;
            text-align: right;
        }

        .items-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .item {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px;
            background: #f8fafc;
        }

        .item-name {
            font-weight: 700;
            margin-bottom: 6px;
        }

        .item-meta {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: #475569;
            font-size: 14px;
        }

        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 100;
        }

        .overlay.show {
            display: flex;
        }

        .modal {
            width: 100%;
            max-width: 430px;
            background: #fff;
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .modal-head {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .modal-foot {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 10px;
        }

        .input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
        }

        .preview {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px;
            white-space: pre-line;
            color: #475569;
        }

        .loading,
        .error {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }

        .error {
            color: #991b1b;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="top-actions">
            <a href="/ventas" class="btn">Volver</a>
            
            @if($salesShowAccess['ticket_print'])
                <a href="/ventas/{{ $saleId }}/ticket" target="_blank" class="btn btn-primary">Ver ticket original</a>
                <a href="/ventas/{{ $saleId }}/ticket?print=1" target="_blank" class="btn">Imprimir ticket original</a>
            @endif
            
            <button class="btn" onclick="openSmsModal()">Enviar por SMS</button>
            
            @if($salesShowAccess['create'])
                <a href="/ventas/pos" class="btn">Nueva venta</a>
            @endif
        </div>

        <div id="saleContent" class="loading">Cargando detalle de venta...</div>
    </div>

    <div class="overlay" id="smsModalOverlay">
        <div class="modal">
            <div class="modal-head">
                <div>
                    <div style="font-size:18px; font-weight:700;">Enviar ticket por SMS</div>
                    <div style="color:#64748b; margin-top:6px;">Comparte el resumen de compra</div>
                </div>
                <button class="btn" onclick="closeSmsModal()">Cerrar</button>
            </div>
            <div class="modal-body">
                <div>
                    <div style="font-weight:600; margin-bottom:8px;">Número de teléfono del cliente</div>
                    <input type="text" id="smsPhone" class="input" placeholder="5512345678">
                </div>

                <div>
                    <div style="font-weight:600; margin-bottom:8px;">Vista previa del mensaje</div>
                    <div class="preview" id="smsPreview"></div>
                </div>
            </div>
            <div class="modal-foot">
                <button class="btn" onclick="closeSmsModal()">Cancelar</button>
                <button class="btn btn-primary" onclick="alert('La integración real de SMS queda pendiente.');">Enviar SMS</button>
            </div>
        </div>
    </div>

    <script>
        const saleId = @json($saleId);
        let saleData = null;

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

        function safe(value, fallback = '-') {
            return value ?? fallback;
        }

        function renderSale(data) {
            saleData = data;

            const itemsHtml = (data.items || []).map(item => `
                <div class="item">
                    <div class="item-name">${safe(item.item_name)}</div>
                    <div class="item-meta">
                        <span>${safe(item.amount, 0)} x ${safe(item.unit_price_display ?? money(item.unit_price))}</span>
                        <strong>${safe(item.total_line_display ?? money(item.total_line))}</strong>
                    </div>
                </div>
            `).join('');

            document.getElementById('saleContent').innerHTML = `
                <div class="grid">
                    <div class="card">
                        <h2>Resumen de venta</h2>

                        <div class="row"><span class="label">Folio</span><span class="value">${safe(data.sale_folio)}</span></div>
                        <div class="row"><span class="label">Fecha</span><span class="value">${safe(data.date_time_display ?? data.date_time)}</span></div>
                        <div class="row"><span class="label">Sucursal</span><span class="value">${safe(data.branch?.name_branch)}</span></div>
                        <div class="row"><span class="label">Cajero</span><span class="value">${safe(data.cashier?.name_user)}</span></div>
                        <div class="row"><span class="label">Cliente</span><span class="value">${safe(data.customer?.name_customer, 'Cliente general')}</span></div>
                        <div class="row"><span class="label">Estado</span><span class="value">${safe(data.status_sale)}</span></div>
                    </div>

                    <div class="card">
                        <h2>Pago</h2>

                        <div class="row"><span class="label">Método</span><span class="value">${safe(data.payment?.payment_method)}</span></div>
                        <div class="row"><span class="label">Estado</span><span class="value">${safe(data.payment?.status_payment)}</span></div>
                        <div class="row"><span class="label">Recibido</span><span class="value">${safe(data.payment?.amount_paid_display ?? money(data.payment?.amount_paid))}</span></div>
                        <div class="row"><span class="label">Cambio</span><span class="value">${safe(data.payment?.change_given_display ?? money(data.payment?.change_given))}</span></div>
                        ${data.payment?.reference_payment ? `
                            <div class="row"><span class="label">Referencia</span><span class="value">${safe(data.payment.reference_payment)}</span></div>
                        ` : ''}

                        <div class="row"><span class="label">Subtotal</span><span class="value">${safe(data.subtotal_display ?? money(data.subtotal))}</span></div>
                        <div class="row"><span class="label">IVA</span><span class="value">${safe(data.iva_display ?? money(data.iva))}</span></div>
                        <div class="row"><span class="label">Descuento</span><span class="value">${safe(data.discount_display ?? money(data.discount))}</span></div>
                        <div class="row"><span class="label">Total</span><span class="value">${safe(data.total_display ?? money(data.total))}</span></div>
                    </div>
                </div>

                <div class="card" style="margin-top:18px;">
                    <h3>Productos vendidos</h3>
                    <div class="items-list">
                        ${itemsHtml || '<div class="loading" style="padding:10px 0;">No hay productos.</div>'}
                    </div>
                </div>
            `;

            document.getElementById('smsPhone').value = data.customer?.phone ?? '';
            document.getElementById('smsPreview').textContent =
                `¡Gracias por tu compra en Punto!\n` +
                `Folio: ${safe(data.sale_folio)}\n` +
                `Total: ${safe(data.total_display ?? money(data.total))}\n` +
                `Fecha: ${safe(data.date_time_display ?? data.date_time)}\n` +
                `Ver ticket completo: /ventas/${saleId}/ticket`;
        }

        async function loadSale() {
            const { response, data } = await apiFetch(`/api/sales/${saleId}`);

            if (!response.ok) {
                document.getElementById('saleContent').innerHTML =
                    `<div class="error">No se pudo cargar la venta.</div>`;
                return;
            }

            renderSale(data);
        }

        function openSmsModal() {
            document.getElementById('smsModalOverlay').classList.add('show');
        }

        function closeSmsModal() {
            document.getElementById('smsModalOverlay').classList.remove('show');
        }

        document.addEventListener('DOMContentLoaded', loadSale);
    </script>
</body>
</html>