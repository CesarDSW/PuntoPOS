@extends('layout.dashboard_design')

@section('title', 'Inventario')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/inventory/index.css') }}">
@endpush

@section('content')

@php
    $inventoryAccess = [
        'view' => \App\Support\UserAccess::has(auth()->user(), 'inventory.view'),
        'adjust' => \App\Support\UserAccess::has(auth()->user(), 'inventory.adjust'),
        'history_view' => \App\Support\UserAccess::has(auth()->user(), 'inventory.history.view'),
    ];
@endphp


<div class="inventory-wrap">
    <div class="inventory-header">
        <div>
            <h1 style="font-size: 32px; margin-bottom: 8px;">Inventario</h1>
            <p class="text-muted">Control y seguimiento de stock de productos.</p>
        </div>

        <div class="inventory-actions">
            @if($inventoryAccess['adjust'])
                <button class="btn btn-primary" type="button" onclick="openBulkModal()">Ajuste general</button>
            @endif
            
            @if($inventoryAccess['history_view'])
                <button class="btn" type="button" onclick="openHistoryModal()">Ver historial</button>
            @endif
        </div>
    </div>

    <div id="inventoryStockAlerts"></div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Total productos</div>
            <div class="summary-value" id="summaryTotalProducts">0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Stock total</div>
            <div class="summary-value" id="summaryStockUnits">0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Stock bajo</div>
            <div class="summary-value" id="summaryLowStock">0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Valor total</div>
            <div class="summary-value" id="summaryInventoryValue">$0</div>
        </div>
    </div>

    <div class="filters-card">
        <input type="text" id="inventorySearch" class="input" placeholder="Buscar por nombre o SKU...">
        <select id="inventoryStatus" class="select">
            <option value="all">Todos los estados</option>
            <option value="normal">Normal</option>
            <option value="low">Stock bajo</option>
            <option value="out">Sin stock</option>
        </select>
    </div>

    <div class="table-card">
        <div class="table-head">
            <div class="table-title">Productos en inventario</div>
            <div class="table-subtitle" id="inventoryCountText">0 productos encontrados</div>
        </div>

        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock actual</th>
                        <th>Stock mínimo</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="inventoryTableBody">
                    <tr>
                        <td colspan="7" class="loading-box">Cargando inventario...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal" id="singleAdjustModal">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title">Ajuste de inventario</div>
                <div class="modal-subtitle">Modifica el stock del producto</div>
            </div>
            <button class="btn" type="button" onclick="closeSingleAdjustModal()">Cerrar</button>
        </div>

        <div class="modal-body">
            <div class="product-box">
                <div style="font-weight: bold; font-size: 18px;" id="singleProductName">Producto</div>
                <div class="text-muted" id="singleProductCode">SKU</div>
                <div style="margin-top: 8px;">Stock actual: <strong id="singleCurrentStock">0</strong></div>
            </div>

            <div class="field-group">
                <label class="field-label">Tipo de ajuste</label>
                <select id="singleType" class="select">
                    <option value="ENTRADA">Entrada</option>
                    <option value="SALIDA">Salida</option>
                </select>
            </div>

            <div class="field-group">
                <label class="field-label">Cantidad</label>
                <input type="number" id="singleQuantity" class="input" min="1" placeholder="0">
            </div>

            <div class="field-group">
                <label class="field-label">Motivo</label>
                <select id="singleReasonSelect" class="select"></select>
            </div>

            <div class="field-group" id="singleReasonOtherWrap" style="display:none;">
                <label class="field-label">Escribe el motivo</label>
                <input type="text" id="singleReasonOther" class="input" maxlength="30" placeholder="Máximo 30 caracteres">
                <small class="text-muted" id="singleReasonCounter" style="display:block; margin-top:4px;">0/30 caracteres</small>
            </div>

            <div class="error-box" id="singleErrorBox"></div>
        </div>

        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeSingleAdjustModal()">Cancelar</button>
            <button class="btn btn-primary" type="button" onclick="submitSingleAdjust()">Confirmar ajuste</button>
        </div>
    </div>
</div>

<div class="modal" id="historyModal">
    <div class="modal-box modal-box-lg">
        <div class="modal-header">
            <div>
                <div class="modal-title">Historial de ajustes</div>
                <div class="modal-subtitle">Registro de movimientos de inventario</div>
            </div>
            <button class="btn" type="button" onclick="closeHistoryModal()">Cerrar</button>
        </div>

        <div class="modal-body">
            <div style="display:grid; grid-template-columns: 1.2fr 1fr 1fr 1fr; gap: 12px;">
                <input type="text" id="historySearch" class="input" placeholder="Buscar producto o motivo...">
                <select id="historyType" class="select">
                    <option value="">Todos los tipos</option>
                    <option value="ENTRADA">Entrada</option>
                    <option value="SALIDA">Salida</option>
                    <option value="AJUSTE">Ajuste</option>
                    <option value="TRANSFERENCIA">Transferencia</option>
                </select>
                <input type="date" id="historyFrom" class="input">
                <input type="date" id="historyTo" class="input">
            </div>

            <div style="display:flex; justify-content:flex-end;">
                <button class="btn" type="button" onclick="loadHistory()">Buscar</button>
            </div>

            <div class="history-list" id="historyList">
                <div class="loading-box">Cargando historial...</div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="bulkModal">
    <div class="modal-box modal-box-lg">
        <div class="modal-header">
            <div>
                <div class="modal-title">Ajuste general de inventario</div>
                <div class="modal-subtitle">Modifica el stock de múltiples productos</div>
            </div>
            <button class="btn" type="button" onclick="closeBulkModal()">Cerrar</button>
        </div>

        <div class="modal-body">
            <div class="field-group">
                <label class="field-label">Tipo de ajuste</label>
                <select id="bulkType" class="select">
                    <option value="ENTRADA">Entrada</option>
                    <option value="SALIDA">Salida</option>
                </select>
            </div>

            <div class="field-group">
                <label class="field-label">Motivo</label>
                <select id="bulkReasonSelect" class="select"></select>
            </div>

            <div class="field-group" id="bulkReasonOtherWrap" style="display:none;">
                <label class="field-label">Escribe el motivo</label>
                <input type="text" id="bulkReasonOther" class="input" maxlength="30" placeholder="Máximo 30 caracteres">
                <small class="text-muted" id="bulkReasonCounter" style="display:block; margin-top:4px;">0/30 caracteres</small>
            </div>

            <div class="field-group">
                <label class="field-label">Buscar producto</label>
                <input type="text" id="bulkSearch" class="input" placeholder="Buscar por nombre o SKU...">
            </div>

            <div class="error-box" id="bulkErrorBox"></div>

            <div class="bulk-list" id="bulkProductsList">
                <div class="loading-box">Cargando productos...</div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeBulkModal()">Cancelar</button>
            <button class="btn btn-primary" type="button" onclick="submitBulkAdjust()">Confirmar ajustes</button>
        </div>
    </div>
</div>

<script>
    const inventoryAccess = @json($inventoryAccess);
    const REASON_MAX_LENGTH = 30;

    let inventoryItems = [];
    let selectedProduct = null;
    let bulkProducts = [];

    function formatMoney(value) {
        return window.appFormat.money(value);
    }

    function showError(boxId, message) {
        const box = document.getElementById(boxId);
        box.textContent = message;
        box.style.display = 'block';
    }

    function hideError(boxId) {
        const box = document.getElementById(boxId);
        box.textContent = '';
        box.style.display = 'none';
    }

    function updateReasonCounter(inputId, counterId) {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(counterId);

        if (!input || !counter) {
            return;
        }

        const length = input.value.length;
        counter.textContent = `${length}/${REASON_MAX_LENGTH} caracteres`;

        if (length >= REASON_MAX_LENGTH) {
            counter.textContent = `${length}/${REASON_MAX_LENGTH} caracteres. Llegaste al límite permitido.`;
        }
    }

    async function apiFetch(url, options = {}) {
        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.headers || {})
                },
                ...options
            });

            const text = await response.text();
            let data = {};

            try {
                data = text ? JSON.parse(text) : {};
            } catch (e) {
                data = {
                    message: text ? text.substring(0, 500) : 'Respuesta no válida del servidor.'
                };
            }

            return { response, data };
        } catch (error) {
            return {
                response: { ok: false, status: 0 },
                data: { message: error.message || 'Error de red.' }
            };
        }
    }

    async function loadInventorySummary() {
        const { response, data } = await apiFetch('/api/inventory/summary');
        if (!response.ok) return;

        document.getElementById('summaryTotalProducts').textContent = data.total_products ?? 0;
        document.getElementById('summaryStockUnits').textContent = data.total_stock_units ?? 0;
        document.getElementById('summaryLowStock').textContent = data.low_stock_count ?? 0;
        document.getElementById('summaryInventoryValue').textContent = formatMoney(data.inventory_value ?? 0);
    }

    async function loadLowStockAlert() {
        const container = document.getElementById('inventoryStockAlerts');

        if (!container) {
            return;
        }

        const { response, data } = await apiFetch('/api/inventory/low-stock?limit=20');

        if (!response.ok || !Array.isArray(data) || data.length === 0) {
            container.innerHTML = '';
            return;
        }

        const outOfStockProducts = data.filter(item => Number(item.stocks || 0) <= 0);

        const lowStockProducts = data.filter(item => {
            const stock = Number(item.stocks || 0);
            const minimum = Number(item.minimum_stock || 0);

            return stock > 0 && minimum > 0 && stock <= minimum;
        });

        let html = '';

        if (outOfStockProducts.length > 0) {
            const items = outOfStockProducts.map(item => `
                <li>${item.name_product}: ${item.stocks} unidades</li>
            `).join('');

            html += `
                <div class="inventory-alert-section">
                    <strong>Producto agotado detectado</strong>
                    <p>${outOfStockProducts.length} producto(s) sin existencias.</p>
                    <ul>
                        ${items}
                    </ul>
                </div>
            `;
        }

        if (lowStockProducts.length > 0) {
            const items = lowStockProducts.map(item => `
                <li>${item.name_product}: ${item.stocks} unidades (mínimo: ${item.minimum_stock})</li>
            `).join('');

            html += `
                <div class="inventory-alert-section">
                    <strong>Stock bajo detectado</strong>
                    <p>${lowStockProducts.length} producto(s) con stock por debajo del mínimo.</p>
                    <ul>
                        ${items}
                    </ul>
                </div>
            `;
        }

        container.innerHTML = html
            ? `<div class="inventory-alert-card">${html}</div>`
            : '';
    }

    async function loadInventoryTable() {
        const search = document.getElementById('inventorySearch').value.trim();
        const status = document.getElementById('inventoryStatus').value;

        const params = new URLSearchParams({
            per_page: 100,
            search: search,
            status: status
        });

        const { response, data } = await apiFetch(`/api/inventory?${params.toString()}`);
        const tbody = document.getElementById('inventoryTableBody');

        if (!response.ok) {
            tbody.innerHTML = `<tr><td colspan="7">No se pudo cargar el inventario.</td></tr>`;
            return;
        }

        inventoryItems = data.data || [];
        document.getElementById('inventoryCountText').textContent = `${inventoryItems.length} producto(s) encontrados`;

        if (inventoryItems.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="empty-box">No hay productos para mostrar.</td></tr>`;
            return;
        }

        tbody.innerHTML = inventoryItems.map(item => {
            let badgeClass = 'badge-normal';
            let badgeText = 'Normal';

            if (item.stock_status === 'low') {
                badgeClass = 'badge-low';
                badgeText = 'Stock bajo';
            }

            if (item.stock_status === 'out') {
                badgeClass = 'badge-out';
                badgeText = 'Sin stock';
            }

            const actions = inventoryAccess.adjust
                ? `<button class="btn btn-primary" type="button" onclick="openSingleAdjustModal(${item.product_id})">Ajustar</button>`
                : `<span class="text-muted">Sin acciones</span>`;


            return `
                <tr>
                    <td>
                        <div style="font-weight:bold;">${item.name_product}</div>
                        <div class="text-muted">SKU: ${item.code_product}</div>
                    </td>
                    <td>${item.name_category ?? '-'}</td>
                    <td>${item.stocks} unidades</td>
                    <td>${item.minimum_stock} unidades</td>
                    <td>${formatMoney(item.price)}</td>
                    <td><span class="badge ${badgeClass}">${badgeText}</span></td>
                    <td>${actions}</td>
                </tr>
            `;
        }).join('');
    }

    async function loadInventoryPage() {
        await Promise.all([
            loadInventorySummary(),
            loadLowStockAlert(),
            loadInventoryTable()
        ]);
    }
    
    async function loadReasonOptions(type, selectId, wrapId, inputId) {
        const { response, data } = await apiFetch(`/api/inventory/reasons?type=${encodeURIComponent(type)}`);
        if (!response.ok) return;

        const select = document.getElementById(selectId);
        const wrap = document.getElementById(wrapId);
        const input = document.getElementById(inputId);

        select.innerHTML = '';

        (data.reasons || []).forEach(reason => {
            const option = document.createElement('option');
            option.value = reason;
            option.textContent = reason;
            select.appendChild(option);
        });

        if (input) input.value = '';
        wrap.style.display = select.value === 'Otro' ? 'block' : 'none';

        updateReasonCounter(inputId, inputId === 'singleReasonOther' ? 'singleReasonCounter' : 'bulkReasonCounter');
    }

    function toggleOtherReason(selectId, wrapId) {
        const select = document.getElementById(selectId);
        const wrap = document.getElementById(wrapId);
        wrap.style.display = select.value === 'Otro' ? 'block' : 'none';

        if (selectId === 'singleReasonSelect') {
            updateReasonCounter('singleReasonOther', 'singleReasonCounter');
        }

        if (selectId === 'bulkReasonSelect') {
            updateReasonCounter('bulkReasonOther', 'bulkReasonCounter');
        }
    }

    async function openSingleAdjustModal(productId) {
        if (!inventoryAccess.adjust) return;

        hideError('singleErrorBox');

        const { response, data } = await apiFetch(`/api/inventory/products/${productId}`);

        if (!response.ok) {
            showError('singleErrorBox', data.message || 'No se pudo cargar el producto.');
            document.getElementById('singleAdjustModal').classList.add('show');
            return;
        }

        selectedProduct = data;

        document.getElementById('singleProductName').textContent = data.name_product;
        document.getElementById('singleProductCode').textContent = `SKU: ${data.code_product}`;
        document.getElementById('singleCurrentStock').textContent = `${data.current_stock} unidades`;
        document.getElementById('singleQuantity').value = '';
        document.getElementById('singleReasonOther').value = '';
        updateReasonCounter('singleReasonOther', 'singleReasonCounter');
        document.getElementById('singleType').value = 'ENTRADA';

        await loadReasonOptions('ENTRADA', 'singleReasonSelect', 'singleReasonOtherWrap', 'singleReasonOther');

        document.getElementById('singleAdjustModal').classList.add('show');
    }

    function closeSingleAdjustModal() {
        document.getElementById('singleAdjustModal').classList.remove('show');
    }

    async function submitSingleAdjust() {
        if (!inventoryAccess.adjust) return;

        hideError('singleErrorBox');

        if (!selectedProduct) {
            showError('singleErrorBox', 'No hay producto seleccionado.');
            return;
        }

        const type = document.getElementById('singleType').value;
        const quantity = parseInt(document.getElementById('singleQuantity').value || '0');
        const selectedReason = document.getElementById('singleReasonSelect').value;
        const otherReason = document.getElementById('singleReasonOther').value.trim();
        const reason = selectedReason === 'Otro' ? otherReason : selectedReason;

        if (!quantity || quantity < 1) {
            showError('singleErrorBox', 'Ingresa una cantidad válida.');
            return;
        }

        if (type === 'SALIDA' && quantity > parseInt(selectedProduct.current_stock)) {
            showError('singleErrorBox', 'La cantidad a sacar es mayor al stock disponible.');
            return;
        }

        if (!reason) {
            showError('singleErrorBox', 'Selecciona o escribe un motivo.');
            return;
        }

        if (reason.length > REASON_MAX_LENGTH) {
            showError('singleErrorBox', `El motivo no debe superar los ${REASON_MAX_LENGTH} caracteres.`);
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const { response, data } = await apiFetch('/api/inventory/adjustments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                product_id: selectedProduct.product_id,
                type: type,
                quantity: quantity,
                reason: reason
            })
        });

        if (!response.ok) {
            showError(
                'singleErrorBox',
                data.errors?.quantity?.[0] ||
                data.errors?.reason?.[0] ||
                data.errors?.product_id?.[0] ||
                data.message ||
                `Error ${response.status}: no se pudo guardar el ajuste.`
            );
            return;
        }

        closeSingleAdjustModal();
        await loadInventoryPage();
    }

    async function openHistoryModal() {
        if (!inventoryAccess.history_view) return;

        document.getElementById('historyModal').classList.add('show');
        await loadHistory();
    }

    function closeHistoryModal() {
        document.getElementById('historyModal').classList.remove('show');
    }

    async function loadHistory() {
        if (!inventoryAccess.history_view) return;

        const search = document.getElementById('historySearch').value.trim();
        const type = document.getElementById('historyType').value;
        const from = document.getElementById('historyFrom').value;
        const to = document.getElementById('historyTo').value;

        const params = new URLSearchParams({ per_page: 50 });
        if (search) params.append('search', search);
        if (type) params.append('type', type);
        if (from) params.append('from', from);
        if (to) params.append('to', to);

        const { response, data } = await apiFetch(`/api/inventory/adjustments?${params.toString()}`);
        const container = document.getElementById('historyList');

        if (!response.ok) {
            container.innerHTML = `<div class="empty-box">${data.message || `Error ${response.status}: no se pudo cargar el historial.`}</div>`;
            return;
        }

        const rows = data.data || [];

        if (rows.length === 0) {
            container.innerHTML = `<div class="empty-box">No hay movimientos registrados.</div>`;
            return;
        }

        container.innerHTML = rows.map(item => `
            <div class="history-item">
                <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start;">
                    <div>
                        <div style="font-weight:bold; font-size:18px;">${item.product_name}</div>
                        <div class="text-muted">${item.reason}</div>
                    </div>
                    <div class="badge ${item.type === 'SALIDA' ? 'badge-out' : 'badge-normal'}">
                        ${item.signed_quantity} unidades
                    </div>
                </div>

                <div class="history-grid">
                    <div>
                        <div class="text-muted">Stock anterior</div>
                        <div style="font-weight:bold;">${item.previous_stock ?? '-'}</div>
                    </div>
                    <div>
                        <div class="text-muted">Stock nuevo</div>
                        <div style="font-weight:bold;">${item.new_stock ?? '-'}</div>
                    </div>
                    <div>
                        <div class="text-muted">Fecha</div>
                        <div style="font-weight:bold;">${window.appFormat.dateTime(item.date_time)}</div>
                    </div>
                    <div>
                        <div class="text-muted">Usuario</div>
                        <div style="font-weight:bold;">${item.user_name}</div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    async function openBulkModal() {
        if (!inventoryAccess.adjust) return;

        hideError('bulkErrorBox');
        document.getElementById('bulkType').value = 'ENTRADA';
        document.getElementById('bulkReasonOther').value = '';
        updateReasonCounter('bulkReasonOther', 'bulkReasonCounter');
        document.getElementById('bulkSearch').value = '';

        await loadReasonOptions('ENTRADA', 'bulkReasonSelect', 'bulkReasonOtherWrap', 'bulkReasonOther');

        const { response, data } = await apiFetch('/api/inventory?per_page=100');

        if (!response.ok) {
            document.getElementById('bulkProductsList').innerHTML = `<div class="empty-box">No se pudieron cargar los productos.</div>`;
            document.getElementById('bulkModal').classList.add('show');
            return;
        }

        bulkProducts = (data.data || []).map(item => ({
            ...item,
            entered_quantity: 0
        }));

        renderBulkProducts();
        document.getElementById('bulkModal').classList.add('show');
    }

    function closeBulkModal() {
        document.getElementById('bulkModal').classList.remove('show');
    }

    function renderBulkProducts() {
        const search = document.getElementById('bulkSearch').value.trim().toLowerCase();
        const container = document.getElementById('bulkProductsList');

        const filtered = bulkProducts.filter(item => {
            const text = `${item.name_product} ${item.code_product}`.toLowerCase();
            return text.includes(search);
        });

        if (filtered.length === 0) {
            container.innerHTML = `<div class="empty-box">No hay productos para mostrar.</div>`;
            return;
        }

        container.innerHTML = filtered.map(item => `
            <div class="bulk-item">
                <div class="bulk-item-row">
                    <div>
                        <div style="font-weight:bold; font-size:18px;">${item.name_product}</div>
                        <div class="text-muted">SKU: ${item.code_product}</div>
                        <div class="text-muted" style="margin-top:6px;">Stock actual: ${item.stocks}</div>
                    </div>

                    <div>
                        <div class="text-muted">Precio</div>
                        <div style="font-weight:bold;">${formatMoney(item.price)}</div>
                    </div>

                    <div>
                        <input
                            type="number"
                            min="0"
                            class="input"
                            value="${item.entered_quantity || 0}"
                            oninput="updateBulkQuantity(${item.product_id}, this.value)"
                        >
                    </div>
                </div>
            </div>
        `).join('');
    }

    function updateBulkQuantity(productId, value) {
        const index = bulkProducts.findIndex(item => item.product_id === productId);
        if (index === -1) return;
        bulkProducts[index].entered_quantity = parseInt(value || '0');
    }

    async function submitBulkAdjust() {
        if (!inventoryAccess.adjust) return;

        hideError('bulkErrorBox');

        const type = document.getElementById('bulkType').value;
        const selectedReason = document.getElementById('bulkReasonSelect').value;
        const otherReason = document.getElementById('bulkReasonOther').value.trim();
        const reason = selectedReason === 'Otro' ? otherReason : selectedReason;

        const items = bulkProducts 
            .filter(item => Number(item.entered_quantity) > 0)
            .map(item => ({
                product_id: item.product_id,
                quantity: Number(item.entered_quantity)
            }));

        if (!reason) {
            showError('bulkErrorBox', 'Selecciona o escribe un motivo.');
            return;
        }

        if (reason.length > REASON_MAX_LENGTH) {
            showError('bulkErrorBox', `El motivo no debe superar los ${REASON_MAX_LENGTH} caracteres.`);
            return;
        }

        if (items.length === 0) {
            showError('bulkErrorBox', 'Debes capturar al menos un producto.');
            return;
        }

        if (type === 'SALIDA') {
            for (const row of items) {
                const product = bulkProducts.find(item => item.product_id === row.product_id);
                if (row.quantity > Number(product.stocks)) {
                    showError('bulkErrorBox', `La salida para ${product.name_product} es mayor al stock disponible.`);
                    return;
                }
            }
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const { response, data } = await apiFetch('/api/inventory/adjustments/bulk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                type: type,
                reason: reason,
                items: items
            })
        });

        if (!response.ok) {
            showError(
                'bulkErrorBox',
                data.errors?.items?.[0] ||
                data.errors?.reason?.[0] ||
                data.message ||
                `Error ${response.status}: no se pudo guardar el ajuste general.`
            );
            return;
        }

        closeBulkModal();
        await loadInventoryPage();
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadInventoryPage();

        document.getElementById('inventorySearch').addEventListener('input', loadInventoryTable);
        document.getElementById('inventoryStatus').addEventListener('change', loadInventoryTable);

        document.getElementById('singleType').addEventListener('change', async function () {
            await loadReasonOptions(this.value, 'singleReasonSelect', 'singleReasonOtherWrap', 'singleReasonOther');
        });

        document.getElementById('singleReasonSelect').addEventListener('change', function () {
            toggleOtherReason('singleReasonSelect', 'singleReasonOtherWrap');
        });

        document.getElementById('singleReasonOther').addEventListener('input', function () {
            updateReasonCounter('singleReasonOther', 'singleReasonCounter');
        });

        document.getElementById('bulkType').addEventListener('change', async function () {
            await loadReasonOptions(this.value, 'bulkReasonSelect', 'bulkReasonOtherWrap', 'bulkReasonOther');
        });

        document.getElementById('bulkReasonSelect').addEventListener('change', function () {
            toggleOtherReason('bulkReasonSelect', 'bulkReasonOtherWrap');
        });

        document.getElementById('bulkReasonOther').addEventListener('input', function () {
            updateReasonCounter('bulkReasonOther', 'bulkReasonCounter');
        });

        document.getElementById('bulkSearch').addEventListener('input', renderBulkProducts);
    });
</script>
@endsection