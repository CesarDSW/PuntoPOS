<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS</title>

    <link rel="stylesheet" href="{{ asset('css/pages/sales/pos.css') }}">
    @stack('styles')
</head>
<body>
<div class="pos-shell">
    <div class="pos-top">
        <div class="top-info">
            <span><span class="dot"></span> <strong>Turno activo</strong></span>
            <span id="posUserName">{{ auth()->user()->name_user }}</span>
            <span id="posBranchName">Sucursal actual</span>
        </div>

        <div class="dropdown-wrap">
            <button class="btn" id="optionsButton">Opciones</button>
            <div class="dropdown-menu" id="optionsMenu">
                <button class="dropdown-item" type="button" onclick="openCloseShiftModal()">Cerrar turno</button>
                <button class="dropdown-item" type="button" onclick="closeCash()">Cerrar caja</button>
            </div>
        </div>
    </div>

    <div class="pos-main">
        <div class="pos-left">
            <div class="customer-box">
                <h2 style="font-size:18px;">Cliente</h2>
                <input
                    type="text"
                    id="customerSearch"
                    class="search-box"
                    placeholder="Buscar cliente por nombre, teléfono o correo..."
                    autocomplete="off"
                    style="margin-top:12px;"
                >
                <div class="customer-results" id="customerResults"></div>
                <div class="selected-customer" id="selectedCustomerCard"></div>
            </div>

            <h2 style="font-size:18px;">Buscar productos</h2>
            <input type="text" id="posSearch" class="search-box" placeholder="Escribe el nombre del producto...">
            <div class="products-grid" id="productsGrid"></div>
        </div>

        <div class="pos-right">
            <div class="cart-head">
                <strong>Venta actual</strong>
                <button type="button" class="btn" style="padding:8px 12px;" onclick="clearCart()">Limpiar</button>
            </div>

            <div class="cart-items" id="cartItems">
                <div class="cart-empty">No hay productos<br>Busca y agrega productos</div>
            </div>

            <div class="cart-footer">
                <div class="total-row"><span>Subtotal</span><strong id="cartSubtotal">$0</strong></div>
                <div class="total-row"><span>IVA (16%)</span><strong id="cartIva">$0</strong></div>
                <div class="total-main"><span>Total</span><span id="cartTotal">$0</span></div>
                <button class="btn btn-primary btn-block" id="payButton" onclick="openPaymentModal()" disabled>Cobrar $0</button>
            </div>
        </div>
    </div>
</div>

<div class="overlay" id="cashModalOverlay">
    <div class="modal">
        <div class="modal-head">
            <div>
                <div style="font-size:28px; font-weight:700;">Apertura de caja</div>
                <div style="color:#64748b; margin-top:6px;">Ingresa el monto inicial para comenzar operaciones</div>
            </div>
        </div>
        <div class="modal-body">
            <div class="field">
                <label class="label">Monto inicial en efectivo</label>
                <input type="number" step="0.01" min="0" id="openingAmount" class="input" placeholder="0.00">
            </div>
            <div class="field">
                <label class="label">Observaciones</label>
                <textarea id="openingNotes" class="textarea" placeholder="Ej: billetes revisados, fondo completo..."></textarea>
            </div>
            <div class="error-box" id="cashErrorBox"></div>
        </div>
        <div class="modal-foot">
            <a href="/ventas" class="btn" style="text-decoration:none;">Cancelar</a>
            <button class="btn btn-primary" type="button" onclick="openCash()">Abrir caja</button>
        </div>
    </div>
</div>

<div class="overlay" id="shiftModalOverlay">
    <div class="modal shift-open-modal">
        <div class="shift-open-body">
            <div class="shift-open-top">
                <div class="shift-open-icon">🕒</div>
                <div class="shift-open-title">Apertura de turno</div>
                <div class="shift-open-subtitle">Confirma el inicio de tu turno de trabajo</div>
            </div>

            <div class="shift-open-alert">
                <div class="shift-open-alert-icon">i</div>
                <div>
                    <div style="font-weight:700; margin-bottom:6px;">Caja abierta</div>
                    <div style="color:#64748b; font-size:14px; line-height:1.45;">
                        La caja del punto de venta ya está abierta. Solo necesitas iniciar tu turno.
                    </div>
                </div>
            </div>

            <div class="shift-open-user-card">
                <div class="shift-open-avatar" id="shiftOpenAvatar">
                    {{ strtoupper(substr(auth()->user()->name_user, 0, 1)) }}
                </div>
                <div>
                    <div class="shift-open-user-name" id="shiftOpenUserName">{{ auth()->user()->name_user }}</div>
                    <div class="shift-open-user-role">Administrador</div>
                </div>
            </div>

            <div class="shift-open-info-card">
                <div class="shift-open-info-icon">🕒</div>
                <div>
                    <div class="shift-open-info-label">Fecha y hora de inicio</div>
                    <div class="shift-open-info-value" id="shiftOpenDateTime">-</div>
                </div>
            </div>

            <div class="shift-open-info-card">
                <div class="shift-open-info-icon">👤</div>
                <div>
                    <div class="shift-open-info-label">Tipo de turno</div>
                    <div class="shift-open-info-value">Turno de cajero</div>
                </div>
            </div>

            <div class="shift-open-checks">
                <div class="shift-open-checks-title">Verificaciones</div>
                <div class="shift-open-check-list">
                    <div class="shift-open-check-item">
                        <span class="shift-open-check-badge">✓</span>
                        <span>Caja abierta y lista</span>
                    </div>
                    <div class="shift-open-check-item">
                        <span class="shift-open-check-badge">✓</span>
                        <span>Área de trabajo preparada</span>
                    </div>
                    <div class="shift-open-check-item">
                        <span class="shift-open-check-badge">✓</span>
                        <span>Conexión a internet estable</span>
                    </div>
                </div>
            </div>

            <input type="hidden" id="shiftType" class="shift-open-hidden-input" value="CAJERO">
            <div class="error-box" id="shiftErrorBox"></div>
        </div>

        <div class="shift-open-foot">
            <a href="/ventas" class="btn" style="text-decoration:none; text-align:center;">Cancelar</a>
            <button class="btn btn-primary" type="button" onclick="openShift()">Iniciar turno</button>
        </div>
    </div>
</div>

<div class="overlay" id="paymentModalOverlay">
    <div class="modal">
        <div class="modal-head">
            <div>
                <div style="font-size:18px; font-weight:700;">Método de pago</div>
            </div>
            <button class="btn" type="button" onclick="closePaymentModal()">Cerrar</button>
        </div>
        <div class="modal-body">
            <div class="pay-method active" data-method="EFECTIVO" onclick="selectMethod('EFECTIVO')" id="methodEFECTIVO">Efectivo</div>
            <div class="pay-method" data-method="TARJETA" onclick="selectMethod('TARJETA')" id="methodTARJETA">Tarjeta</div>
            <div class="pay-method" data-method="TRANSFERENCIA" onclick="selectMethod('TRANSFERENCIA')" id="methodTRANSFERENCIA">Transferencia</div>

            <div class="info-card">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span>Cliente</span>
                    <strong id="paymentCustomerText">Cliente general</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Total a cobrar</span>
                    <strong id="paymentTotalText">$0</strong>
                </div>
            </div>

            <div id="cashFields">
                <div class="field">
                    <label class="label">Monto recibido</label>
                    <input type="number" step="0.01" min="0" id="amountPaid" class="input" placeholder="0.00" oninput="renderPaymentTotals()">
                </div>

                <div class="info-card">
                    <div style="display:flex; justify-content:space-between;">
                        <span>Cambio</span>
                        <strong id="changeGivenText">$0</strong>
                    </div>
                </div>
            </div>

            <div id="referenceWrap" style="display:none;">
                <div class="field">
                    <label class="label">Referencia</label>
                    <input type="text" id="referencePayment" class="input" placeholder="Opcional">
                </div>
            </div>

            <div class="error-box" id="paymentErrorBox"></div>
        </div>
        <div class="modal-foot">
            <button class="btn" type="button" onclick="closePaymentModal()">Cancelar</button>
            <button class="btn btn-primary" type="button" id="confirmSaleButton" onclick="confirmSale()">Confirmar</button>
        </div>
    </div>
</div>

<div class="overlay" id="successModalOverlay">
    <div class="modal">
        <div class="modal-head">
            <div>
                <div style="font-size:32px; font-weight:700;">¡Venta confirmada!</div>
                <div style="color:#64748b; margin-top:6px;">La transacción se ha procesado exitosamente</div>
            </div>
        </div>
        <div class="modal-body">
            <div class="info-card" id="successInfo"></div>
            <div class="info-card" id="successItems"></div>
        </div>
        <div class="modal-foot" style="flex-direction:column;">
            <button class="btn btn-primary btn-block" type="button" id="viewTicketBtn">Ver e imprimir ticket</button>
            <button class="btn btn-block" type="button" onclick="startNewSale()">Nueva venta</button>
        </div>
    </div>
</div>

<div class="overlay" id="closeShiftOverlay">
    <div class="modal modal-large">
        <div class="modal-head">
            <div>
                <div style="font-size:28px; font-weight:700;">Cierre de turno</div>
                <div style="color:#64748b; margin-top:6px;">Resumen de actividad del turno</div>
            </div>
            <button class="btn" type="button" onclick="closeCloseShiftModal()">Cerrar</button>
        </div>

        <div class="modal-body">
            <div class="shift-top-grid">
                <div>
                    <div style="color:#64748b; margin-bottom:8px;">Usuario</div>
                    <div style="font-size:18px; font-weight:700;" id="closeShiftUserName">-</div>

                    <div style="color:#64748b; margin-top:18px; margin-bottom:8px;">Inicio</div>
                    <div style="font-size:18px; font-weight:700;" id="closeShiftStartTime">-</div>
                </div>

                <div>
                    <div style="color:#64748b; margin-bottom:8px;">Duración</div>
                    <div style="font-size:18px; font-weight:700;" id="closeShiftDuration">-</div>

                    <div style="color:#64748b; margin-top:18px; margin-bottom:8px;">Fin</div>
                    <div style="font-size:18px; font-weight:700;" id="closeShiftEndTime">-</div>
                </div>
            </div>

            <div class="shift-summary-grid">
                <div class="shift-summary-card">
                    <div style="color:#64748b; margin-bottom:10px;">Total vendido</div>
                    <div style="font-size:22px; font-weight:700;" id="closeShiftTotalSold">$0</div>
                </div>

                <div class="shift-summary-card">
                    <div style="color:#64748b; margin-bottom:10px;">Ventas realizadas</div>
                    <div style="font-size:22px; font-weight:700;" id="closeShiftSalesCount">0</div>
                </div>

                <div class="shift-summary-card">
                    <div style="color:#64748b; margin-bottom:10px;">Ticket promedio</div>
                    <div style="font-size:22px; font-weight:700;" id="closeShiftAvgTicket">$0</div>
                </div>
            </div>

            <div class="method-box">
                <div style="font-size:22px; font-weight:700; margin-bottom:14px;">Ventas por método de pago</div>

                <div class="method-row">
                    <div><span class="method-dot dot-card"></span>Tarjeta</div>
                    <strong id="closeShiftCardCount">0 ventas</strong>
                </div>

                <div class="method-row">
                    <div><span class="method-dot dot-cash"></span>Efectivo</div>
                    <strong id="closeShiftCashCount">0 ventas</strong>
                </div>

                <div class="method-row">
                    <div><span class="method-dot dot-transfer"></span>Transferencia</div>
                    <strong id="closeShiftTransferCount">0 ventas</strong>
                </div>
            </div>

            <div class="field">
                <label class="label">Observaciones del turno (opcional)</label>
                <textarea id="closeShiftNotes" class="textarea" placeholder="Agrega comentarios sobre el turno..."></textarea>
            </div>

            <div class="info-card">
                <strong>Importante:</strong> Al cerrar tu turno, la caja permanecerá abierta para que puedas cerrarla más tarde desde el sistema o desde el POS.
            </div>

            <div class="error-box" id="closeShiftErrorBox"></div>
        </div>

        <div class="modal-foot">
            <button class="btn" type="button" onclick="closeCloseShiftModal()">Cancelar</button>
            <button class="btn btn-primary" type="button" onclick="confirmCloseShift()">Confirmar cierre de turno</button>
        </div>
    </div>
</div>

<div class="overlay" id="shiftClosedSuccessOverlay">
    <div class="modal shift-closed-modal">
        <div class="shift-closed-body">
            <div class="shift-closed-top">
                <div class="shift-closed-icon">✓</div>
                <div class="shift-closed-title">Turno cerrado<br>correctamente</div>
                <div class="shift-closed-subtitle">
                    Tu turno ha finalizado. La caja permanecerá abierta.
                </div>
            </div>

            <div class="shift-closed-summary">
                <div class="shift-closed-summary-title">
                    <span class="shift-closed-summary-icon">🕒</span>
                    <span>Resumen del turno</span>
                </div>

                <div class="shift-closed-divider"></div>

                <div class="shift-closed-row">
                    <span class="shift-closed-label">Usuario</span>
                    <span class="shift-closed-value" id="shiftClosedUser">-</span>
                </div>

                <div class="shift-closed-row">
                    <span class="shift-closed-label">Duración</span>
                    <span class="shift-closed-value" id="shiftClosedDuration">-</span>
                </div>

                <div class="shift-closed-row">
                    <span class="shift-closed-label">Ventas realizadas</span>
                    <span class="shift-closed-value" id="shiftClosedSalesCount">0</span>
                </div>

                <div class="shift-closed-total">
                    <span>Total vendido</span>
                    <span class="shift-closed-total-amount" id="shiftClosedTotalSold">$0</span>
                </div>
            </div>

            <div class="shift-closed-note">
                <strong>La caja permanece abierta:</strong> Puedes cerrarla después desde la pantalla de ventas o volver a iniciar turno.
            </div>

            <div class="shift-closed-actions">
                <button class="btn btn-primary btn-block" type="button" onclick="backToSystem()">
                    ← Volver al sistema
                </button>
                <button class="btn btn-block" type="button" onclick="restartShiftFlow()">
                    Iniciar nuevo turno
                </button>
            </div>
        </div>
    </div>
</div>
<div class="overlay" id="closeCashOverlay">
    <div class="modal">
        <div class="modal-head">
            <div>
                <div style="font-size:28px; font-weight:700;">Cierre de caja</div>
                <div style="color:#64748b; margin-top:6px;">
                    Ingresa el monto final registrado al terminar operaciones
                </div>
            </div>
            <button class="btn" type="button" onclick="closeCashModal()">Cerrar</button>
        </div>

        <div class="modal-body">
            <div class="info-card">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span>Usuario</span>
                    <strong id="closeCashUserName">-</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Sucursal</span>
                    <strong id="closeCashBranchName">-</strong>
                </div>
            </div>

            <div class="field">
                <label class="label">Monto final de caja</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    id="closeCashAmount"
                    class="input"
                    placeholder="0.00"
                >
            </div>

            <div class="field">
                <label class="label">Observaciones (opcional)</label>
                <textarea
                    id="closeCashNotes"
                    class="textarea"
                    placeholder="Ej: sobrante, faltante, corte revisado..."
                ></textarea>
            </div>

            <div class="error-box" id="closeCashErrorBox"></div>
        </div>

        <div class="modal-foot">
            <button class="btn" type="button" onclick="closeCashModal()">Cancelar</button>
            <button class="btn btn-primary" type="button" onclick="confirmCloseCash()">
                Confirmar cierre
            </button>
        </div>
    </div>
</div>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let cart = [];
    let productsCache = [];
    let customersCache = [];
    let selectedCustomer = null;
    let paymentMethod = 'EFECTIVO';
    let lastSaleId = null;
    let currentShiftSummary = null;

    function money(value) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            maximumFractionDigits: 2
        }).format(Number(value || 0));
    }

    function showError(id, message) {
        const box = document.getElementById(id);
        box.textContent = message;
        box.style.display = 'block';
    }

    function hideError(id) {
        const box = document.getElementById(id);
        box.textContent = '';
        box.style.display = 'none';
    }

    function getUserInitials(name) {
        const parts = String(name || '')
            .trim()
            .split(/\s+/)
            .filter(Boolean);

        if (parts.length === 0) return 'U';
        if (parts.length === 1) return parts[0].substring(0, 1).toUpperCase();

        return (parts[0][0] + parts[1][0]).toUpperCase();
    }

    function formatShiftOpenDateTime(date = new Date()) {
        const months = [
            'ene', 'feb', 'mar', 'abr', 'may', 'jun',
            'jul', 'ago', 'sep', 'oct', 'nov', 'dic'
        ];

        const day = date.getDate();
        const month = months[date.getMonth()];
        const year = date.getFullYear();

        let hours = date.getHours();
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const suffix = hours >= 12 ? 'p.m.' : 'a.m.';

        hours = hours % 12;
        if (hours === 0) hours = 12;

        return `${day} ${month} ${year} - ${hours}:${minutes} ${suffix}`;
    }

    function prepareShiftOpenModal() {
        const userName = document.getElementById('posUserName').textContent.trim() || '{{ auth()->user()->name_user }}';
        document.getElementById('shiftOpenUserName').textContent = userName;
        document.getElementById('shiftOpenAvatar').textContent = getUserInitials(userName);
        document.getElementById('shiftOpenDateTime').textContent = formatShiftOpenDateTime(new Date());
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

    function cartSubtotal() {
        return cart.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
    }

    function cartIva() {
        return cartSubtotal() * 0.16;
    }

    function cartTotal() {
        return cartSubtotal() + cartIva();
    }

    function renderSelectedCustomer() {
        const card = document.getElementById('selectedCustomerCard');

        if (!selectedCustomer) {
            card.innerHTML = `
                <div class="selected-customer-label">Cliente seleccionado</div>
                <div class="selected-customer-top">
                    <div>
                        <div class="selected-customer-name">Cliente general</div>
                        <div class="selected-customer-meta">Se usará automáticamente si no eliges otro cliente.</div>
                    </div>
                </div>
            `;
            return;
        }

        card.innerHTML = `
            <div class="selected-customer-label">Cliente seleccionado</div>
            <div class="selected-customer-top">
                <div>
                    <div class="selected-customer-name">${selectedCustomer.name_customer}</div>
                    <div class="selected-customer-meta">
                        ${selectedCustomer.phone || 'Sin teléfono'} · ${selectedCustomer.email || 'Sin correo'}
                    </div>
                </div>
                <button type="button" class="selected-customer-clear" onclick="clearSelectedCustomer()">Quitar</button>
            </div>
        `;
    }

    function hideCustomerResults() {
        document.getElementById('customerResults').classList.remove('show');
    }

    function renderCustomerResults(items) {
        const results = document.getElementById('customerResults');

        if (!items.length) {
            results.innerHTML = `<div class="customer-empty">No se encontraron clientes.</div>`;
            results.classList.add('show');
            return;
        }

        results.innerHTML = items.map(item => `
            <button type="button" class="customer-option" onclick="selectCustomer(${item.customer_id})">
                <div class="customer-option-name">${item.name_customer}</div>
                <div class="customer-option-meta">${item.phone || 'Sin teléfono'} · ${item.email || 'Sin correo'}</div>
            </button>
        `).join('');

        results.classList.add('show');
    }

    async function loadCustomers() {
        const search = document.getElementById('customerSearch').value.trim();
        const { response, data } = await apiFetch(`/api/sales/pos/customers?search=${encodeURIComponent(search)}&limit=10`);

        if (!response.ok) {
            customersCache = [];
            hideCustomerResults();
            return;
        }

        customersCache = Array.isArray(data) ? data : [];
        renderCustomerResults(customersCache);
    }

    function selectCustomer(customerId) {
        const customer = customersCache.find(item => Number(item.customer_id) === Number(customerId));
        if (!customer) return;

        selectedCustomer = customer;
        document.getElementById('customerSearch').value = customer.name_customer;
        renderSelectedCustomer();
        hideCustomerResults();
    }

    function clearSelectedCustomer() {
        selectedCustomer = null;
        document.getElementById('customerSearch').value = '';
        renderSelectedCustomer();
        hideCustomerResults();
    }

    function renderCart() {
        const container = document.getElementById('cartItems');
        const subtotal = cartSubtotal();
        const iva = cartIva();
        const total = cartTotal();

        document.getElementById('cartSubtotal').textContent = money(subtotal);
        document.getElementById('cartIva').textContent = money(iva);
        document.getElementById('cartTotal').textContent = money(total);

        const payButton = document.getElementById('payButton');
        payButton.disabled = cart.length === 0;
        payButton.textContent = `Cobrar ${money(total)}`;

        if (cart.length === 0) {
            container.innerHTML = `<div class="cart-empty">No hay productos<br>Busca y agrega productos</div>`;
            return;
        }

        container.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="cart-item-top">
                    <div>
                        <div class="cart-item-name">${item.name_product}</div>
                        <div class="cart-item-controls">
                            <button class="qty-btn" onclick="decreaseQty(${item.product_id})">−</button>
                            <strong>${item.quantity}</strong>
                            <button class="qty-btn" onclick="increaseQty(${item.product_id})">+</button>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <button class="trash-btn" onclick="removeItem(${item.product_id})">🗑️</button>
                        <div style="font-weight:700; margin-top:10px;">${money(item.price * item.quantity)}</div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function renderProducts(items, searchText = '') {
        const grid = document.getElementById('productsGrid');
        const trimmedSearch = String(searchText || '').trim();

        if (trimmedSearch === '') {
            grid.innerHTML = ``;
            return;
        }

        if (!items.length) {
            grid.innerHTML = `
                <div class="products-empty">
                    No se encontraron productos con la búsqueda realizada.
                </div>
            `;
            return;
        }

        grid.innerHTML = items.map(item => `
            <div class="product-card" onclick="addProduct(${item.product_id})">
                <div class="product-name">${item.name_product}</div>
                <div class="product-price">${money(item.price)}</div>
                <div class="product-stock">Stock: ${item.stocks}</div>
            </div>
        `).join('');
    }

    function addProduct(productId) {
        const product = productsCache.find(p => Number(p.product_id) === Number(productId));
        if (!product) return;

        const existing = cart.find(item => Number(item.product_id) === Number(productId));

        if (existing) {
            if (existing.quantity < Number(product.stocks)) {
                existing.quantity += 1;
            }
        } else {
            cart.push({
                product_id: Number(product.product_id),
                name_product: product.name_product,
                price: Number(product.price),
                max_stock: Number(product.stocks),
                quantity: 1
            });
        }

        renderCart();
    }

    function increaseQty(productId) {
        const item = cart.find(x => x.product_id === Number(productId));
        if (!item) return;
        if (item.quantity < item.max_stock) item.quantity += 1;
        renderCart();
    }

    function decreaseQty(productId) {
        const item = cart.find(x => x.product_id === Number(productId));
        if (!item) return;

        item.quantity -= 1;
        if (item.quantity <= 0) {
            cart = cart.filter(x => x.product_id !== Number(productId));
        }

        renderCart();
    }

    function removeItem(productId) {
        cart = cart.filter(x => x.product_id !== Number(productId));
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function renderShiftClosedSuccess(summary) {
        document.getElementById('shiftClosedUser').textContent = summary?.user_name ?? '-';
        document.getElementById('shiftClosedDuration').textContent = summary?.duration_text ?? '-';
        document.getElementById('shiftClosedSalesCount').textContent = summary?.sales_count ?? 0;
        document.getElementById('shiftClosedTotalSold').textContent = money(summary?.total_sold ?? 0);
    }

    function closeShiftClosedSuccessModal() {
        document.getElementById('shiftClosedSuccessOverlay').classList.remove('show');
    }

    function backToSystem() {
        window.location.href = '/ventas';
    }

    function restartShiftFlow() {
        closeShiftClosedSuccessModal();
        prepareShiftOpenModal();
        document.getElementById('shiftModalOverlay').classList.add('show');
    }

    async function loadStatus() {
        const { response, data } = await apiFetch('/api/sales/pos/status');

        if (!response.ok) {
            alert(data.message || 'No se pudo cargar el POS.');
            window.location.href = '/ventas';
            return;
        }

        document.getElementById('posBranchName').textContent = data.branch?.name_branch ?? 'Sucursal actual';
        document.getElementById('posUserName').textContent = data.user?.name_user ?? '{{ auth()->user()->name_user }}';

        if (!data.cash_session) {
            document.getElementById('cashModalOverlay').classList.add('show');
            return;
        }

        if (!data.active_shift && data.system_shift && Number(data.system_shift.userr_idfk) !== Number(data.user?.userr_id)) {
            alert('Ya existe un turno abierto por otro usuario. Debes esperar a que cierre su turno.');
            window.location.href = '/ventas';
            return;
        }

        if (!data.active_shift) {
            prepareShiftOpenModal();
            document.getElementById('shiftModalOverlay').classList.add('show');
            return;
        }

        productsCache = [];
        renderProducts([], '');
    }

    async function loadProducts() {
        const search = document.getElementById('posSearch').value.trim();

        if (search === '') {
            productsCache = [];
            renderProducts([], '');
            return;
        }

        const { response, data } = await apiFetch(`/api/sales/pos/products?search=${encodeURIComponent(search)}`);

        if (!response.ok) {
            productsCache = [];
            renderProducts([], search);
            return;
        }

        productsCache = Array.isArray(data) ? data : [];
        renderProducts(productsCache, search);
    }

    async function openCash() {
        hideError('cashErrorBox');

        const opening_amount = document.getElementById('openingAmount').value;
        const notes_opening = document.getElementById('openingNotes').value.trim();

        const { response, data } = await apiFetch('/api/sales/cash/open', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ opening_amount, notes_opening })
        });

        if (!response.ok) {
            showError(
                'cashErrorBox',
                data.errors?.cash_session?.[0] ||
                data.errors?.opening_amount?.[0] ||
                data.message ||
                'No se pudo abrir la caja.'
            );
            return;
        }

        document.getElementById('cashModalOverlay').classList.remove('show');
        prepareShiftOpenModal();
        document.getElementById('shiftModalOverlay').classList.add('show');
    }

    async function openShift() {
        hideError('shiftErrorBox');

        const shift_type = document.getElementById('shiftType').value.trim() || 'CAJERO';

        const { response, data } = await apiFetch('/api/sales/shifts/open', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ shift_type })
        });

        if (!response.ok) {
            showError(
                'shiftErrorBox',
                data.errors?.shift?.[0] ||
                data.message ||
                'No se pudo iniciar el turno.'
            );
            return;
        }

        document.getElementById('shiftModalOverlay').classList.remove('show');
        productsCache = [];
        renderProducts([], '');
    }

    async function openCloseShiftModal() {
        hideError('closeShiftErrorBox');
        document.getElementById('closeShiftNotes').value = '';

        const { response, data } = await apiFetch('/api/sales/shifts/summary');

        if (!response.ok) {
            alert(data.message || 'No se pudo cargar el resumen del turno.');
            return;
        }

        currentShiftSummary = data;

        document.getElementById('closeShiftUserName').textContent = data.user_name ?? '-';
        document.getElementById('closeShiftStartTime').textContent = data.start_time_label ?? '-';
        document.getElementById('closeShiftEndTime').textContent = data.end_time_label ?? '-';
        document.getElementById('closeShiftDuration').textContent = data.duration_text ?? '-';

        document.getElementById('closeShiftTotalSold').textContent = money(data.total_sold ?? 0);
        document.getElementById('closeShiftSalesCount').textContent = data.sales_count ?? 0;
        document.getElementById('closeShiftAvgTicket').textContent = money(data.avg_ticket ?? 0);

        document.getElementById('closeShiftCardCount').textContent = `${data.payment_methods?.TARJETA ?? 0} ventas`;
        document.getElementById('closeShiftCashCount').textContent = `${data.payment_methods?.EFECTIVO ?? 0} ventas`;
        document.getElementById('closeShiftTransferCount').textContent = `${data.payment_methods?.TRANSFERENCIA ?? 0} ventas`;

        document.getElementById('closeShiftOverlay').classList.add('show');
    }

    function closeCloseShiftModal() {
        document.getElementById('closeShiftOverlay').classList.remove('show');
    }

    async function confirmCloseShift() {
        hideError('closeShiftErrorBox');

        const notes_shift = document.getElementById('closeShiftNotes').value.trim();

        const { response, data } = await apiFetch('/api/sales/shifts/close', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ notes_shift })
        });

        if (!response.ok) {
            showError(
                'closeShiftErrorBox',
                data.errors?.shift?.[0] ||
                data.message ||
                'No se pudo cerrar el turno.'
            );
            return;
        }

        closeCloseShiftModal();

        cart = [];
        renderCart();
        productsCache = [];
        renderProducts([], '');
        document.getElementById('posSearch').value = '';
        clearSelectedCustomer();

        renderShiftClosedSuccess(currentShiftSummary || {
            user_name: document.getElementById('posUserName').textContent.trim(),
            duration_text: '-',
            sales_count: 0,
            total_sold: 0
        });

        document.getElementById('shiftClosedSuccessOverlay').classList.add('show');
    }

    function closeCash() {
    hideError('closeCashErrorBox');

    document.getElementById('closeCashUserName').textContent =
        document.getElementById('posUserName').textContent.trim() || 'Usuario';

    document.getElementById('closeCashBranchName').textContent =
        document.getElementById('posBranchName').textContent.trim() || 'Sucursal actual';

    document.getElementById('closeCashAmount').value = '';
    document.getElementById('closeCashNotes').value = '';

    document.getElementById('closeCashOverlay').classList.add('show');
}

function closeCashModal() {
    document.getElementById('closeCashOverlay').classList.remove('show');
}

async function confirmCloseCash() {
    hideError('closeCashErrorBox');

    const closingAmount = document.getElementById('closeCashAmount').value.trim();
    const notesClosing = document.getElementById('closeCashNotes').value.trim();

    if (closingAmount === '' || Number(closingAmount) < 0) {
        showError('closeCashErrorBox', 'Ingresa un monto final válido.');
        return;
    }

    const { response, data } = await apiFetch('/api/sales/cash/close', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            closing_amount: closingAmount,
            notes_closing: notesClosing
        })
    });

    if (!response.ok) {
        showError(
            'closeCashErrorBox',
            data.errors?.shift?.[0] ||
            data.errors?.closing_amount?.[0] ||
            data.message ||
            'No se pudo cerrar la caja.'
        );
        return;
    }

    closeCashModal();
    window.location.href = data.redirect_url || ('/ventas/cajas/' + data.cash_session_id);
}

    function openPaymentModal() {
        if (!cart.length) return;

        hideError('paymentErrorBox');
        paymentMethod = 'EFECTIVO';

        document.getElementById('amountPaid').value = '';
        document.getElementById('referencePayment').value = '';
        document.getElementById('paymentTotalText').textContent = money(cartTotal());
        document.getElementById('paymentCustomerText').textContent = selectedCustomer?.name_customer || 'Cliente general';

        document.querySelectorAll('.pay-method').forEach(el => el.classList.remove('active'));
        document.getElementById('methodEFECTIVO').classList.add('active');

        document.getElementById('cashFields').style.display = 'block';
        document.getElementById('referenceWrap').style.display = 'none';

        renderPaymentTotals();
        document.getElementById('paymentModalOverlay').classList.add('show');
    }

    function closePaymentModal() {
        document.getElementById('paymentModalOverlay').classList.remove('show');
    }

    function selectMethod(method) {
        paymentMethod = method;
        document.querySelectorAll('.pay-method').forEach(el => el.classList.remove('active'));
        document.getElementById('method' + method).classList.add('active');

        if (method === 'EFECTIVO') {
            document.getElementById('cashFields').style.display = 'block';
            document.getElementById('referenceWrap').style.display = 'none';
        } else {
            document.getElementById('cashFields').style.display = 'none';
            document.getElementById('referenceWrap').style.display = 'block';
        }

        renderPaymentTotals();
    }

    function renderPaymentTotals() {
        const total = cartTotal();
        let change = 0;
        let canConfirm = true;

        if (paymentMethod === 'EFECTIVO') {
            const amountPaid = Number(document.getElementById('amountPaid').value || 0);
            change = Math.max(amountPaid - total, 0);
            canConfirm = amountPaid >= total && total > 0;
        } else {
            canConfirm = total > 0;
        }

        document.getElementById('changeGivenText').textContent = money(change);
        document.getElementById('paymentTotalText').textContent = money(total);
        document.getElementById('confirmSaleButton').disabled = !canConfirm;
    }

    async function confirmSale() {
        hideError('paymentErrorBox');

        if (!cart.length) {
            showError('paymentErrorBox', 'Agrega al menos un producto.');
            return;
        }

        const payload = {
            payment_method: paymentMethod,
            items: cart.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity
            }))
        };

        if (selectedCustomer?.customer_id) {
            payload.customer_id = selectedCustomer.customer_id;
        }

        if (paymentMethod === 'EFECTIVO') {
            payload.amount_paid = document.getElementById('amountPaid').value || 0;
        } else {
            payload.reference_payment = document.getElementById('referencePayment').value.trim();
        }

        const { response, data } = await apiFetch('/api/sales', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            showError(
                'paymentErrorBox',
                data.errors?.amount_paid?.[0] ||
                data.errors?.customer_id?.[0] ||
                data.errors?.items?.[0] ||
                data.errors?.shift?.[0] ||
                data.message ||
                'No se pudo registrar la venta.'
            );
            return;
        }

        closePaymentModal();

        const sale = data.data;
        const customerName = selectedCustomer?.name_customer || 'Cliente general';
        lastSaleId = sale.sale_id;

        document.getElementById('successInfo').innerHTML = `
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Folio de venta</span><strong>${sale.sale_folio}</strong></div>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Cliente</span><strong>${customerName}</strong></div>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Método de pago</span><strong>${sale.payment_method}</strong></div>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Productos</span><strong>${sale.items_count} items</strong></div>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Recibido</span><strong>${money(sale.amount_paid)}</strong></div>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Cambio</span><strong>${money(sale.change_given)}</strong></div>
            <div style="display:flex; justify-content:space-between;"><span>Total</span><strong>${money(sale.total)}</strong></div>
        `;

        document.getElementById('successItems').innerHTML = `
            <div style="font-weight:700; margin-bottom:10px;">PRODUCTOS VENDIDOS</div>
            ${sale.items.map(item => `
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span>${item.quantity}x ${item.product_name}</span>
                    <strong>${money(item.line_subtotal)}</strong>
                </div>
            `).join('')}
        `;

        document.getElementById('viewTicketBtn').onclick = function () {
            window.location.href = '/ventas/' + lastSaleId;
        };

        cart = [];
        renderCart();
        productsCache = [];
        renderProducts([], '');
        document.getElementById('posSearch').value = '';
        clearSelectedCustomer();
        document.getElementById('successModalOverlay').classList.add('show');
    }

    function startNewSale() {
        document.getElementById('successModalOverlay').classList.remove('show');
        clearCart();
        productsCache = [];
        renderProducts([], '');
        document.getElementById('posSearch').value = '';
        clearSelectedCustomer();
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderCart();
        renderSelectedCustomer();
        loadStatus();

        const posSearch = document.getElementById('posSearch');
        const customerSearch = document.getElementById('customerSearch');
        const customerResults = document.getElementById('customerResults');

        posSearch.addEventListener('input', loadProducts);

        customerSearch.addEventListener('input', loadCustomers);
        customerSearch.addEventListener('focus', loadCustomers);

        const optionsButton = document.getElementById('optionsButton');
        const optionsMenu = document.getElementById('optionsMenu');

        optionsButton.addEventListener('click', function () {
            optionsMenu.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            if (!optionsButton.contains(e.target) && !optionsMenu.contains(e.target)) {
                optionsMenu.classList.remove('show');
            }

            if (!customerSearch.contains(e.target) && !customerResults.contains(e.target)) {
                hideCustomerResults();
            }
        });
        const closeCashOverlay = document.getElementById('closeCashOverlay');

closeCashOverlay.addEventListener('click', function (e) {
    if (e.target === closeCashOverlay) {
        closeCashModal();
    }
});
    });
</script>
</body>
</html>