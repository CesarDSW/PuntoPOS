<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de venta</title>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; font-family:Arial, sans-serif; }
        body { background:#f6f7fb; color:#0f172a; }
        .wrap { max-width:900px; margin:24px auto; padding:0 16px; }
        .top-actions {
            background:#fff; border:1px solid #e5e7eb; border-radius:16px;
            padding:14px; display:flex; gap:12px; flex-wrap:wrap; margin-bottom:18px;
        }
        .btn {
            padding:12px 16px; border-radius:12px; border:1px solid #d1d5db;
            background:#fff; cursor:pointer; text-decoration:none; color:#0f172a;
        }
        .btn-primary { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
        .ticket-card {
            background:#fff; border:1px solid #e5e7eb; border-radius:18px;
            padding:28px; display:flex; justify-content:center;
        }
        .ticket {
            width:100%; max-width:420px; background:#fff; color:#111;
            font-family:monospace; line-height:1.8;
        }
        .ticket-center { text-align:center; }
        .ticket-line { border-top:2px dashed #222; margin:16px 0; }
        .ticket-row { display:flex; justify-content:space-between; gap:12px; }
        .ticket-title { font-size:28px; font-weight:700; margin-bottom:10px; }

        .overlay {
            position:fixed; inset:0; background:rgba(15,23,42,.45);
            display:none; align-items:center; justify-content:center; padding:20px; z-index:100;
        }
        .overlay.show { display:flex; }
        .modal {
            width:100%; max-width:430px; background:#fff; border-radius:18px;
            border:1px solid #e5e7eb; overflow:hidden;
        }
        .modal-head {
            padding:20px; border-bottom:1px solid #e5e7eb;
            display:flex; justify-content:space-between; align-items:center;
        }
        .modal-body { padding:20px; display:flex; flex-direction:column; gap:14px; }
        .modal-foot { padding:20px; border-top:1px solid #e5e7eb; display:flex; gap:10px; }
        .input {
            width:100%; padding:12px 14px; border:1px solid #d1d5db; border-radius:12px;
        }
        .preview {
            background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px; padding:12px;
            white-space:pre-line; color:#475569;
        }

        @media print {
            body { background:#fff; }
            .top-actions, .overlay { display:none !important; }
            .wrap { max-width:none; margin:0; padding:0; }
            .ticket-card { border:none; border-radius:0; padding:0; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top-actions">
        <a href="/ventas" class="btn">Volver</a>
        <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
        <button class="btn" onclick="openSmsModal()">Enviar por SMS</button>
        <a href="{{ route('factura.generar', $saleId) }}" class="btn btn-primary"> Factura</a>
        <a href="/ventas/pos" class="btn">Nueva venta</a>
       
    </div>

    <div class="ticket-card">
        <div class="ticket" id="ticketContent">
            Cargando ticket...
        </div>
    </div>
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

    async function loadSale() {
        const { response, data } = await apiFetch(`/api/sales/${saleId}`);
        const ticket = document.getElementById('ticketContent');

        if (!response.ok) {
            ticket.innerHTML = 'No se pudo cargar la venta.';
            return;
        }

        saleData = data;

        const itemsHtml = data.items.map(item => `
            <div style="margin-bottom:10px;">
                <div>${item.item_name}</div>
                <div class="ticket-row">
                    <span>${item.amount} x ${money(item.unit_price)}</span>
                    <strong>${money(item.total_line)}</strong>
                </div>
            </div>
        `).join('');

        ticket.innerHTML = `
            <div class="ticket-center">
                <div class="ticket-title">PUNTO</div>
                <div>Sistema de gestión</div>
                <div>${data.branch.name_branch}</div>
            </div>

            <div class="ticket-line"></div>

            <div class="ticket-row"><span>FOLIO:</span><strong>${data.sale_folio}</strong></div>
            <div class="ticket-row"><span>FECHA:</span><strong>${data.date_time}</strong></div>
            <div class="ticket-row"><span>CAJERO:</span><strong>${data.cashier.name_user}</strong></div>
            <div class="ticket-row"><span>CLIENTE:</span><strong>${data.customer.name_customer}</strong></div>

            <div class="ticket-line"></div>

            <div style="font-weight:700; margin-bottom:10px;">PRODUCTOS:</div>
            ${itemsHtml}

            <div class="ticket-line"></div>

            <div class="ticket-row"><span>SUBTOTAL:</span><strong>${money(data.subtotal)}</strong></div>
            <div class="ticket-row"><span>IVA (16%):</span><strong>${money(data.iva)}</strong></div>
            <div class="ticket-row"><span>DESCUENTO:</span><strong>${money(data.discount)}</strong></div>
            <div class="ticket-row" style="font-size:20px;"><span>TOTAL:</span><strong>${money(data.total)}</strong></div>

            <div class="ticket-line"></div>

            <div class="ticket-row"><span>MÉTODO DE PAGO:</span><strong>${data.payment.payment_method}</strong></div>
            <div class="ticket-row"><span>RECIBIDO:</span><strong>${money(data.payment.amount_paid)}</strong></div>
            <div class="ticket-row"><span>CAMBIO:</span><strong>${money(data.payment.change_given)}</strong></div>

            <div class="ticket-line"></div>

            <div class="ticket-center">
                <div>¡Gracias por su compra!</div>
                <div>Conserve este ticket</div>
            </div>
        `;

        document.getElementById('smsPhone').value = data.customer.phone ?? '';
        document.getElementById('smsPreview').textContent =
            `¡Gracias por tu compra en Punto!\n` +
            `Folio: ${data.sale_folio}\n` +
            `Total: ${money(data.total)}\n` +
            `Fecha: ${data.date_time}\n` +
            `Ver ticket completo: punto.com/ticket/${data.sale_folio}`;
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