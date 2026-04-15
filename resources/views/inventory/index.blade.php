@extends('layouts.app')

@section('title', 'Inventario')

@section('content')
<style>
    .inventory-wrap { display: flex; flex-direction: column; gap: 18px; }
    .inventory-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
    .inventory-actions { display: flex; gap: 10px; flex-wrap: wrap; }

    .btn-primary {
        background: #1d4ed8;
        color: white;
        border-color: #1d4ed8;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .summary-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px;
    }

    .summary-label {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .summary-value {
        font-size: 32px;
        font-weight: bold;
    }

    .filters-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 16px;
        display: grid;
        grid-template-columns: 1fr 240px;
        gap: 14px;
    }

    .input, .select {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        padding: 10px 12px;
        background: white;
        font-size: 14px;
    }

    .table-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
    }

    .table-head {
        padding: 18px;
        border-bottom: 1px solid #e5e7eb;
    }

    .table-title {
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 6px;
    }

    .table-subtitle {
        color: #6b7280;
        font-size: 14px;
    }

    table { width: 100%; border-collapse: collapse; }
    th, td {
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        font-size: 14px;
        vertical-align: middle;
    }

    th {
        font-size: 12px;
        color: #6b7280;
        background: #f9fafb;
        text-transform: uppercase;
    }

    .badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        border: 1px solid transparent;
    }

    .badge-normal { background: #eef2ff; color: #1d4ed8; }
    .badge-low { background: #fef3c7; color: #b45309; }
    .badge-out { background: #fee2e2; color: #b91c1c; }

    .alert-box {
        background: #fefce8;
        border: 1px solid #fde68a;
        border-left: 4px solid #f59e0b;
        border-radius: 12px;
        padding: 14px 16px;
    }

    .modal {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.35);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        z-index: 1000;
    }

    .modal.show { display: flex; }

    .modal-box {
        width: 100%;
        max-width: 720px;
        max-height: 90vh;
        overflow: auto;
        background: white;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
    }

    .modal-box-lg { max-width: 920px; }

    .modal-header {
        padding: 18px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .modal-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 4px;
    }

    .modal-subtitle {
        font-size: 14px;
        color: #6b7280;
    }

    .modal-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .modal-footer {
        padding: 18px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .product-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px;
    }

    .field-group { display: flex; flex-direction: column; gap: 6px; }
    .field-label { font-size: 14px; font-weight: 600; }

    .error-box {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        border-radius: 10px;
        padding: 10px 12px;
        display: none;
    }

    .history-list, .bulk-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .history-item, .bulk-item {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px;
        background: #f9fafb;
    }

    .history-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-top: 10px;
    }

    .bulk-item-row {
        display: grid;
        grid-template-columns: 2fr 1fr 120px;
        gap: 14px;
        align-items: center;
    }

    .empty-box {
        padding: 18px;
        border: 1px dashed #d1d5db;
        border-radius: 12px;
        text-align: center;
        color: #6b7280;
    }

    .loading-box {
        padding: 18px;
        color: #6b7280;
    }

    @media (max-width: 1000px) {
        .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .filters-card { grid-template-columns: 1fr; }
        .history-grid, .bulk-item-row { grid-template-columns: 1fr; }
    }
</style>

<div class="inventory-wrap">
    <div class="inventory-header">
        <div>
            <h1 style="font-size: 32px; margin-bottom: 8px;">Inventario</h1>
            <p class="text-muted">Control y seguimiento de stock de productos.</p>
        </div>

        <div class="inventory-actions">
            <button class="btn btn-primary" type="button" onclick="openBulkModal()">Ajuste general</button>
            <button class="btn" type="button" onclick="openHistoryModal()">Ver historial</button>
        </div>
    </div>

    <div id="lowStockAlert"></div>

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
                <input type="text" id="singleReasonOther" class="input" placeholder="Escribe el motivo">
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
                <input type="text" id="bulkReasonOther" class="input" placeholder="Escribe el motivo">
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
    let inventoryItems = [];
    let selectedProduct = null;
    let bulkProducts = [];

    function formatMoney(value) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            maximumFractionDigits: 0
        }).format(Number(value || 0));
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
        const { response, data } = await apiFetch('/api/inventory/low-stock?limit=5');
        const container = document.getElementById('lowStockAlert');

        if (!response.ok || !Array.isArray(data) || data.length === 0) {
            container.innerHTML = '';
            return;
        }

        const items = data.map(item => `
            <li>${item.name_product}: ${item.stocks} unidades (mínimo: ${item.minimum_stock})</li>
        `).join('');

        container.innerHTML = `
            <div class="alert-box">
                <div style="font-weight:bold; margin-bottom:8px;">Stock bajo detectado</div>
                <div class="text-muted" style="margin-bottom:8px;">
                    ${data.length} producto(s) con stock por debajo del mínimo
                </div>
                <ul style="padding-left:18px; display:flex; flex-direction:column; gap:4px;">
                    ${items}
                </ul>
            </div>
        `;
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
                    <td>
                        <button class="btn btn-primary" type="button" onclick="openSingleAdjustModal(${item.product_id})">
                            Ajustar
                        </button>
                    </td>
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
    }

    function toggleOtherReason(selectId, wrapId) {
        const select = document.getElementById(selectId);
        const wrap = document.getElementById(wrapId);
        wrap.style.display = select.value === 'Otro' ? 'block' : 'none';
    }

    async function openSingleAdjustModal(productId) {
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
        document.getElementById('singleType').value = 'ENTRADA';

        await loadReasonOptions('ENTRADA', 'singleReasonSelect', 'singleReasonOtherWrap', 'singleReasonOther');

        document.getElementById('singleAdjustModal').classList.add('show');
    }

    function closeSingleAdjustModal() {
        document.getElementById('singleAdjustModal').classList.remove('show');
    }

    async function submitSingleAdjust() {
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
        document.getElementById('historyModal').classList.add('show');
        await loadHistory();
    }

    function closeHistoryModal() {
        document.getElementById('historyModal').classList.remove('show');
    }

    async function loadHistory() {
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
                        <div style="font-weight:bold;">${item.date_time}</div>
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
        hideError('bulkErrorBox');
        document.getElementById('bulkType').value = 'ENTRADA';
        document.getElementById('bulkReasonOther').value = '';
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
        hideError('bulkErrorBox');

        const type = document.getElementById('bulkType').value;
        const selectedReason = document.getElementById('bulkReasonSelect').value;
        const otherReason = document.getElementById('bulkReasonOther').value.trim();
        const reason = selectedReason === 'Otro' ? otherReason : selectedReason;

        if (!reason) {
            showError('bulkErrorBox', 'Selecciona o escribe un motivo.');
            return;
        }

        const items = bulkProducts
            .filter(item => Number(item.entered_quantity) > 0)
            .map(item => ({
                product_id: item.product_id,
                quantity: Number(item.entered_quantity)
            }));

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

        document.getElementById('bulkType').addEventListener('change', async function () {
            await loadReasonOptions(this.value, 'bulkReasonSelect', 'bulkReasonOtherWrap', 'bulkReasonOther');
        });

        document.getElementById('bulkReasonSelect').addEventListener('change', function () {
            toggleOtherReason('bulkReasonSelect', 'bulkReasonOtherWrap');
        });

        document.getElementById('bulkSearch').addEventListener('input', renderBulkProducts);
    });
</script>
@endsection