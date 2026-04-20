@extends('layouts.app')

@section('title', 'Catálogo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/catalog/index.css') }}">
@endpush

@section('content')
<div class="catalog-wrap">
    <div class="catalog-header">
        <div>
            <h1 style="font-size: 32px; margin-bottom: 8px;">Catálogo</h1>
            <p class="text-muted">Administra productos, servicios y categorías.</p>
        </div>

        <div class="catalog-actions">
            <button class="btn btn-primary" type="button" onclick="openItemModal('product')">Nuevo producto</button>
            <button class="btn" type="button" onclick="openItemModal('service')">Nuevo servicio</button>
            <button class="btn" type="button" onclick="openCategoryModal()">Nueva categoría</button>
            <button class="btn" type="button" onclick="openBulkModal()">Carga masiva</button>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Total catálogo</div>
            <div class="summary-value" id="catalogTotalItems">0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Productos</div>
            <div class="summary-value" id="catalogProductsCount">0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Servicios</div>
            <div class="summary-value" id="catalogServicesCount">0</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Activos</div>
            <div class="summary-value" id="catalogActiveCount">0</div>
        </div>
    </div>

    <div class="filters-card">
        <input type="text" id="catalogSearch" class="input" placeholder="Buscar por nombre, código o descripción...">

        <select id="catalogType" class="select">
            <option value="all">Todos los tipos</option>
            <option value="product">Productos</option>
            <option value="service">Servicios</option>
        </select>

        <select id="catalogCategory" class="select">
            <option value="">Todas las categorías</option>
        </select>

        <select id="catalogStatus" class="select">
            <option value="all">Todos los estados</option>
            <option value="active">Activos</option>
            <option value="inactive">Inactivos</option>
        </select>
    </div>

    <div class="section-grid">
        <div class="card">
            <div class="card-head">
                <div class="card-title">Productos y servicios</div>
                <div class="card-subtitle" id="catalogCountText">0 elementos encontrados</div>
            </div>

            <div style="overflow:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Nombre</th>
                            <th>Código</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Costo</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="catalogTableBody">
                        <tr>
                            <td colspan="9" class="text-muted">Cargando catálogo...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div class="card-title">Categorías</div>
                <div class="card-subtitle">Agrupa productos y servicios</div>
            </div>

            <div style="overflow:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Items</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="categoryTableBody">
                        <tr>
                            <td colspan="4" class="text-muted">Cargando categorías...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="itemModal">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title" id="itemModalTitle">Nuevo elemento</div>
                <div class="modal-subtitle" id="itemModalSubtitle">Completa los datos del formulario</div>
            </div>
            <button class="btn" type="button" onclick="closeItemModal()">Cerrar</button>
        </div>

        <div class="modal-body">
            <div class="form-grid">
                <div class="field-group">
                    <label class="field-label">Tipo</label>
                    <select id="itemType" class="select">
                        <option value="product">Producto</option>
                        <option value="service">Servicio</option>
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Estado</label>
                    <select id="itemStatus" class="select">
                        <option value="active">Activo</option>
                        <option value="inactive">Inactivo</option>
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label" id="itemNameLabel">Nombre</label>
                    <input type="text" id="itemName" class="input" placeholder="Nombre" maxlength="80">
                </div>

                <div class="field-group">
                    <label class="field-label" id="itemCodeLabel">Código / SKU</label>
                    <input type="text" id="itemCode" class="input" placeholder="Código" maxlength="15">
                </div>

                <div class="field-group">
                    <label class="field-label">Categoría</label>
                    <select id="itemCategory" class="select"></select>
                </div>

                <div class="field-group">
                    <label class="field-label">Precio</label>
                    <input type="number" step="0.01" min="0" id="itemPrice" class="input" placeholder="0.00">
                </div>

                <div class="field-group" id="itemCostWrap">
                    <label class="field-label">Costo</label>
                    <input type="number" step="0.01" min="0" id="itemCost" class="input" placeholder="0.00">
                </div>

                <div class="field-group" id="itemStockWrap">
                    <label class="field-label">Stock inicial</label>
                    <input type="number" min="0" id="itemStockInitial" class="input" placeholder="0">
                </div>

                <div class="field-group" id="itemMinimumStockWrap">
                    <label class="field-label">Stock mínimo</label>
                    <input type="number" min="0" id="itemMinimumStock" class="input" placeholder="0">
                </div>

                <div class="field-group" style="grid-column: 1 / -1;">
                    <label class="field-label">Descripción</label>
                    <textarea id="itemDescription" class="textarea" placeholder="Descripción opcional" maxlength="250"></textarea>
                </div>
            </div>

            <div class="error-box" id="itemErrorBox"></div>
        </div>

        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeItemModal()">Cancelar</button>
            <button class="btn btn-primary" type="button" onclick="submitItemForm()">Guardar</button>
        </div>
    </div>
</div>

<div class="modal" id="categoryModal">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title" id="categoryModalTitle">Nueva categoría</div>
                <div class="modal-subtitle">Crea o edita una categoría</div>
            </div>
            <button class="btn" type="button" onclick="closeCategoryModal()">Cerrar</button>
        </div>

        <div class="modal-body">
            <div class="field-group">
                <label class="field-label">Nombre</label>
                <input type="text" id="categoryName" class="input" placeholder="Nombre de la categoría" maxlength="15">
            </div>

            <div class="field-group">
                <label class="field-label">Descripción</label>
                <textarea id="categoryDescription" class="textarea" placeholder="Descripción opcional" maxlength="250"></textarea>
            </div>

            <div class="field-group">
                <label class="field-label">Estado</label>
                <select id="categoryStatus" class="select">
                    <option value="active">Activa</option>
                    <option value="inactive">Inactiva</option>
                </select>
            </div>

            <div class="error-box" id="categoryErrorBox"></div>
        </div>

        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeCategoryModal()">Cancelar</button>
            <button class="btn btn-primary" type="button" onclick="submitCategoryForm()">Guardar</button>
        </div>
    </div>
</div>

<div class="modal" id="bulkUploadModal">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title">Carga masiva</div>
                <div class="modal-subtitle">Sube productos y servicios desde una plantilla Excel</div>
            </div>
            <button class="btn" type="button" onclick="closeBulkModal()">Cerrar</button>
        </div>

        <div class="modal-body">
            <div class="field-group">
                <label class="field-label">Paso 1</label>
                <a class="link-btn" href="/api/catalog/bulk-upload/template">Descargar plantilla</a>
            </div>

            <div class="field-group">
                <label class="field-label">Paso 2</label>
                <input type="file" id="bulkFile" class="input" accept=".xlsx,.xls,.csv">
            </div>

            <div class="text-muted">
                La plantilla acepta filas de productos y servicios. Para productos se puede incluir stock inicial y stock mínimo.
            </div>

            <div class="error-box" id="bulkUploadErrorBox"></div>
        </div>

        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeBulkModal()">Cancelar</button>
            <button class="btn btn-primary" type="button" onclick="submitBulkUpload()">Subir archivo</button>
        </div>
    </div>
</div>
<div class="modal" id="confirmActionModal">
    <div class="modal-box confirm-modal-box">
        <div class="modal-header confirm-modal-header">
            <div>
                <div class="confirm-modal-title" id="confirmActionTitle">Confirmar acción</div>
                <div class="confirm-modal-subtitle" id="confirmActionSubtitle">Verifica antes de continuar</div>
            </div>

            <button class="btn confirm-close-btn" type="button" onclick="closeConfirmModal()">Cerrar</button>
        </div>

        <div class="modal-body confirm-modal-body">
            <div class="confirm-info-card">
                <div class="confirm-info-row">
                    <span class="confirm-info-label">Acción</span>
                    <strong id="confirmActionInfo">Acción del sistema</strong>
                </div>

                <div class="confirm-info-row">
                    <span class="confirm-info-label">Estado</span>
                    <strong id="confirmActionStatus">Listo para continuar</strong>
                </div>
            </div>

            <div class="confirm-message-wrap">
                <label class="confirm-message-label">Mensaje</label>
                <div class="confirm-message-box" id="confirmActionMessage">
                    ¿Deseas continuar con esta acción?
                </div>
            </div>
        </div>

        <div class="modal-footer confirm-modal-footer">
            <button class="btn confirm-cancel-btn" type="button" onclick="closeConfirmModal()">Cancelar</button>
            <button class="btn btn-primary confirm-accept-btn" type="button" id="confirmActionButton">Confirmar</button>
        </div>
    </div>
</div>

<script>
    let catalogItems = [];
    let categories = [];
    let editingItem = null;
    let editingCategory = null;

    function formatMoney(value) {
        if (value === null || value === undefined || value === '') return '-';
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            maximumFractionDigits: 2
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

    async function loadCatalogSummary() {
        const { response, data } = await apiFetch('/api/catalog/summary');
        if (!response.ok) return;

        document.getElementById('catalogTotalItems').textContent = data.total_items ?? 0;
        document.getElementById('catalogProductsCount').textContent = data.products_count ?? 0;
        document.getElementById('catalogServicesCount').textContent = data.services_count ?? 0;
        document.getElementById('catalogActiveCount').textContent = data.active_count ?? 0;
    }

    async function loadCategories() {
        const { response, data } = await apiFetch('/api/categories');
        const table = document.getElementById('categoryTableBody');
        const filter = document.getElementById('catalogCategory');
        const formSelect = document.getElementById('itemCategory');

        if (!response.ok) {
            table.innerHTML = `<tr><td colspan="4">No se pudieron cargar las categorías.</td></tr>`;
            return;
        }

        categories = Array.isArray(data) ? data : [];

        filter.innerHTML = `<option value="">Todas las categorías</option>`;
        formSelect.innerHTML = '';

        categories.forEach(category => {
            const option1 = document.createElement('option');
            option1.value = category.category_id;
            option1.textContent = category.name_category;
            filter.appendChild(option1);

            const option2 = document.createElement('option');
            option2.value = category.category_id;
            option2.textContent = category.name_category;
            formSelect.appendChild(option2);
        });

        if (categories.length === 0) {
            table.innerHTML = `<tr><td colspan="4" class="empty-box">No hay categorías registradas.</td></tr>`;
            return;
        }

        table.innerHTML = categories.map(category => {
            const statusBadge = Number(category.status_category) === 1
                ? '<span class="badge badge-green">Activa</span>'
                : '<span class="badge badge-red">Inactiva</span>';

            let actions = '';

            if (category.can_edit) {
                actions += `<button class="btn" type="button" onclick="editCategory(${category.category_id})">Editar</button>`;
            }

            if (category.can_delete) {
                actions += `<button class="btn btn-danger" type="button" onclick="deleteCategory(${category.category_id})">Eliminar</button>`;
            }

            if (!actions) {
                actions = `<span class="text-muted">Sin acciones</span>`;
            }

            return `
                <tr>
                    <td>
                        <div style="font-weight:bold;">${category.name_category}</div>
                        <div class="text-muted">${category.description_category ?? '-'}</div>
                    </td>
                    <td>${category.items_count}</td>
                    <td>${statusBadge}</td>
                    <td><div class="table-actions">${actions}</div></td>
                </tr>
            `;
        }).join('');
    }

    async function loadCatalogItems() {
        const search = document.getElementById('catalogSearch').value.trim();
        const type = document.getElementById('catalogType').value;
        const categoryId = document.getElementById('catalogCategory').value;
        const status = document.getElementById('catalogStatus').value;

        const params = new URLSearchParams({
            per_page: 100,
            type: type,
            status: status
        });

        if (search) params.append('search', search);
        if (categoryId) params.append('category_id', categoryId);

        const { response, data } = await apiFetch(`/api/catalog/items?${params.toString()}`);
        const tbody = document.getElementById('catalogTableBody');

        if (!response.ok) {
            tbody.innerHTML = `<tr><td colspan="9">No se pudo cargar el catálogo.</td></tr>`;
            return;
        }

        catalogItems = data.data || [];
        document.getElementById('catalogCountText').textContent = `${catalogItems.length} elemento(s) encontrados`;

        if (catalogItems.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="empty-box">No hay elementos para mostrar.</td></tr>`;
            return;
        }

        tbody.innerHTML = catalogItems.map(item => {
            const typeBadge = item.item_type === 'product'
                ? '<span class="badge badge-blue">Producto</span>'
                : '<span class="badge badge-yellow">Servicio</span>';

            const statusBadge = Number(item.status) === 1
                ? '<span class="badge badge-green">Activo</span>'
                : '<span class="badge badge-red">Inactivo</span>';

            const editFn = item.item_type === 'product'
                ? `editProduct(${item.item_id})`
                : `editService(${item.item_id})`;

            const deleteFn = item.item_type === 'product'
                ? `deleteProduct(${item.item_id})`
                : `deleteService(${item.item_id})`;

            return `
                <tr>
                    <td>${typeBadge}</td>
                    <td>
                        <div style="font-weight:bold;">${item.name}</div>
                        <div class="text-muted">${item.description ?? '-'}</div>
                    </td>
                    <td>${item.code ?? '-'}</td>
                    <td>${item.category_name ?? '-'}</td>
                    <td>${formatMoney(item.price)}</td>
                    <td>${item.item_type === 'product' ? formatMoney(item.cost) : '-'}</td>
                    <td>${item.item_type === 'product' ? item.stock_display : 'N/A'}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn" type="button" onclick="${editFn}">Editar</button>
                            <button class="btn btn-warning" type="button" onclick="${item.item_type === 'product' ? `deactivateProduct(${item.item_id})` : `deactivateService(${item.item_id})`}">Desactivar</button>
                            <button class="btn btn-danger" type="button" onclick="${deleteFn}">Eliminar</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function loadCatalogPage() {
        await loadCatalogSummary();
        await loadCategories();
        await loadCatalogItems();
    }

    function toggleItemFields() {
        const type = editingItem ? editingItem.type : document.getElementById('itemType').value;
        const isProduct = type === 'product';
        const isEditing = !!editingItem;

        document.getElementById('itemCostWrap').style.display = isProduct ? 'flex' : 'none';
        document.getElementById('itemStockWrap').style.display = (isProduct && !isEditing) ? 'flex' : 'none';
        document.getElementById('itemMinimumStockWrap').style.display = isProduct ? 'flex' : 'none';

        document.getElementById('itemCodeLabel').textContent = isProduct ? 'Código / SKU' : 'Código';
        document.getElementById('itemNameLabel').textContent = isProduct ? 'Nombre del producto' : 'Nombre del servicio';
        document.getElementById('itemType').disabled = isEditing;

        if (!isProduct) {
            document.getElementById('itemCost').value = '';
            document.getElementById('itemStockInitial').value = '';
            document.getElementById('itemMinimumStock').value = '';
        }
    }

    function resetItemForm() {
        editingItem = null;
        hideError('itemErrorBox');

        document.getElementById('itemModalTitle').textContent = 'Nuevo elemento';
        document.getElementById('itemModalSubtitle').textContent = 'Si el SKU del producto ya existe en otra sucursal, se asignará a la sucursal actual sin duplicarlo.';

        document.getElementById('itemType').disabled = false;
        document.getElementById('itemType').value = 'product';
        document.getElementById('itemStatus').value = 'active';
        document.getElementById('itemName').value = '';
        document.getElementById('itemCode').value = '';
        document.getElementById('itemPrice').value = '';
        document.getElementById('itemCost').value = '';
        document.getElementById('itemStockInitial').value = '';
        document.getElementById('itemMinimumStock').value = '';
        document.getElementById('itemDescription').value = '';

        if (categories.length > 0) {
            document.getElementById('itemCategory').value = categories[0].category_id;
        }

        toggleItemFields();
    }

    function openItemModal(type = 'product') {
        resetItemForm();
        document.getElementById('itemType').value = type;

        document.getElementById('itemModalSubtitle').textContent = type === 'product'
            ? 'Si el SKU del producto ya existe en otra sucursal, se asignará a la sucursal actual sin duplicarlo.'
            : 'Completa los datos del formulario';

        toggleItemFields();
        document.getElementById('itemModal').classList.add('show');
    }

    function closeItemModal() {
        document.getElementById('itemModal').classList.remove('show');
    }

    async function editProduct(id) {
        hideError('itemErrorBox');
        const { response, data } = await apiFetch(`/api/products/${id}`);

        if (!response.ok) {
            showError('itemErrorBox', data.message || data.errors?.product_id?.[0] || 'No se pudo cargar el producto.');
            document.getElementById('itemModal').classList.add('show');
            return;
        }

        editingItem = { id, type: 'product' };

        document.getElementById('itemModalTitle').textContent = 'Editar producto';
        document.getElementById('itemModalSubtitle').textContent = 'Actualiza la información del producto en la sucursal actual';

        document.getElementById('itemType').value = 'product';
        document.getElementById('itemStatus').value = Number(data.status_product) === 1 ? 'active' : 'inactive';
        document.getElementById('itemName').value = data.name_product ?? '';
        document.getElementById('itemCode').value = data.code_product ?? '';
        document.getElementById('itemCategory').value = data.category_idfk ?? '';
        document.getElementById('itemPrice').value = data.price ?? '';
        document.getElementById('itemCost').value = data.cost ?? '';
        document.getElementById('itemStockInitial').value = '';
        document.getElementById('itemMinimumStock').value = data.minimum_stock ?? '';
        document.getElementById('itemDescription').value = data.description_product ?? '';

        toggleItemFields();
        document.getElementById('itemModal').classList.add('show');
    }

    async function editService(id) {
        hideError('itemErrorBox');
        const { response, data } = await apiFetch(`/api/services/${id}`);

        if (!response.ok) {
            showError('itemErrorBox', data.message || 'No se pudo cargar el servicio.');
            document.getElementById('itemModal').classList.add('show');
            return;
        }

        editingItem = { id, type: 'service' };

        document.getElementById('itemModalTitle').textContent = 'Editar servicio';
        document.getElementById('itemModalSubtitle').textContent = 'Actualiza la información del servicio';

        document.getElementById('itemType').value = 'service';
        document.getElementById('itemStatus').value = Number(data.status_service) === 1 ? 'active' : 'inactive';
        document.getElementById('itemName').value = data.name_service ?? '';
        document.getElementById('itemCode').value = data.code_service ?? '';
        document.getElementById('itemCategory').value = data.category_idfk ?? '';
        document.getElementById('itemPrice').value = data.price ?? '';
        document.getElementById('itemCost').value = '';
        document.getElementById('itemStockInitial').value = '';
        document.getElementById('itemMinimumStock').value = '';
        document.getElementById('itemDescription').value = data.description_service ?? '';

        toggleItemFields();
        document.getElementById('itemModal').classList.add('show');
    }

    async function submitItemForm() {
        hideError('itemErrorBox');

        const type = editingItem ? editingItem.type : document.getElementById('itemType').value;
        const isProduct = type === 'product';

        const name = document.getElementById('itemName').value.trim();
        const code = document.getElementById('itemCode').value.trim();
        const description = document.getElementById('itemDescription').value.trim();

        if (!name) {
            showError('itemErrorBox', 'El nombre es obligatorio.');
            return;
        }

        if (name.length > 80) {
            showError('itemErrorBox', 'El nombre no puede tener más de 80 caracteres.');
            return;
        }

        if (code.length > 15) {
            showError('itemErrorBox', 'El código no puede tener más de 15 caracteres.');
            return;
        }

        if (description.length > 250) {
            showError('itemErrorBox', 'La descripción no puede tener más de 250 caracteres.');
            return;
        }

        const payload = {
            [isProduct ? 'name_product' : 'name_service']: name,
            [isProduct ? 'code_product' : 'code_service']: code,
            category_id: Number(document.getElementById('itemCategory').value),
            price: document.getElementById('itemPrice').value,
            status: document.getElementById('itemStatus').value,
            [isProduct ? 'description_product' : 'description_service']: description
        };

        if (isProduct) {
            payload.cost = document.getElementById('itemCost').value;
            if (!editingItem) {
                payload.stock_initial = document.getElementById('itemStockInitial').value || 0;
            }
            payload.minimum_stock = document.getElementById('itemMinimumStock').value || 0;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let url = isProduct ? '/api/products' : '/api/services';
        let method = 'POST';

        if (editingItem) {
            url = isProduct
                ? `/api/products/${editingItem.id}`
                : `/api/services/${editingItem.id}`;
            method = 'PUT';
        }

        const { response, data } = await apiFetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            showError(
                'itemErrorBox',
                data.errors?.name_product?.[0] ||
                data.errors?.name_service?.[0] ||
                data.errors?.code_product?.[0] ||
                data.errors?.code_service?.[0] ||
                data.errors?.category_id?.[0] ||
                data.errors?.price?.[0] ||
                data.errors?.cost?.[0] ||
                data.errors?.minimum_stock?.[0] ||
                data.message ||
                `Error ${response.status}: no se pudo guardar el elemento.`
            );
            return;
        }

        closeItemModal();
        await loadCatalogPage();

        if (isProduct && data.reused_existing) {
            alert(data.message || 'El producto ya existía y fue asignado a la sucursal actual.');
        }
    }

    function resetCategoryForm() {
        editingCategory = null;
        hideError('categoryErrorBox');

        document.getElementById('categoryModalTitle').textContent = 'Nueva categoría';
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryDescription').value = '';
        document.getElementById('categoryStatus').value = 'active';
    }

    function openCategoryModal() {
        resetCategoryForm();
        document.getElementById('categoryModal').classList.add('show');
    }

    function closeCategoryModal() {
        document.getElementById('categoryModal').classList.remove('show');
    }

    async function editCategory(id) {
        hideError('categoryErrorBox');
        const { response, data } = await apiFetch(`/api/categories/${id}`);

        if (!response.ok) {
            showError('categoryErrorBox', data.message || 'No se pudo cargar la categoría.');
            document.getElementById('categoryModal').classList.add('show');
            return;
        }

        editingCategory = { id };

        document.getElementById('categoryModalTitle').textContent = 'Editar categoría';
        document.getElementById('categoryName').value = data.name_category ?? '';
        document.getElementById('categoryDescription').value = data.description_category ?? '';
        document.getElementById('categoryStatus').value = Number(data.status_category ?? 1) === 1 ? 'active' : 'inactive';

        document.getElementById('categoryModal').classList.add('show');
    }

    async function submitCategoryForm() {
        hideError('categoryErrorBox');

        const name = document.getElementById('categoryName').value.trim();
        const description = document.getElementById('categoryDescription').value.trim();

        if (!name) {
            showError('categoryErrorBox', 'El nombre es obligatorio.');
            return;
        }

        if (name.length > 15) {
            showError('categoryErrorBox', 'El nombre no puede tener más de 15 caracteres.');
            return;
        }

        if (description.length > 250) {
            showError('categoryErrorBox', 'La descripción no puede tener más de 250 caracteres.');
            return;
        }

        const payload = {
            name_category: name,
            description_category: description,
            status: document.getElementById('categoryStatus').value
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let url = '/api/categories';
        let method = 'POST';

        if (editingCategory) {
            url = `/api/categories/${editingCategory.id}`;
            method = 'PUT';
        }

        const { response, data } = await apiFetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            showError(
                'categoryErrorBox',
                data.errors?.name_category?.[0] ||
                data.message ||
                `Error ${response.status}: no se pudo guardar la categoría.`
            );
            return;
        }

        closeCategoryModal();
        await loadCatalogPage();
    }

    let confirmActionCallback = null;

function openConfirmModal(
    message,
    onConfirm,
    title = 'Confirmar acción',
    subtitle = 'Verifica antes de continuar',
    actionText = 'Acción del sistema',
    statusText = 'Listo para continuar',
    buttonText = 'Confirmar'
) {
    document.getElementById('confirmActionTitle').textContent = title;
    document.getElementById('confirmActionSubtitle').textContent = subtitle;
    document.getElementById('confirmActionInfo').textContent = actionText;
    document.getElementById('confirmActionStatus').textContent = statusText;
    document.getElementById('confirmActionMessage').textContent = message;
    document.getElementById('confirmActionButton').textContent = buttonText;

    document.getElementById('confirmActionModal').classList.add('show');
    confirmActionCallback = onConfirm;
}

function closeConfirmModal() {
    document.getElementById('confirmActionModal').classList.remove('show');
    confirmActionCallback = null;
}

document.addEventListener('DOMContentLoaded', function () {
    const confirmModal = document.getElementById('confirmActionModal');
    const confirmButton = document.getElementById('confirmActionButton');

    if (confirmButton) {
        confirmButton.addEventListener('click', async function () {
            if (typeof confirmActionCallback === 'function') {
                const action = confirmActionCallback;
                closeConfirmModal();
                await action();
            }
        });
    }

    if (confirmModal) {
        confirmModal.addEventListener('click', function (e) {
            if (e.target === confirmModal) {
                closeConfirmModal();
            }
        });
    }
});

    async function deactivateProduct(id) {
    openConfirmModal(
        '¿Deseas desactivar este producto en la sucursal actual?',
        async () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const { response, data } = await apiFetch(`/api/products/${id}/deactivate`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            if (!response.ok) {
                alert(data.errors?.product_id?.[0] || data.message || 'No se pudo desactivar el producto.');
                return;
            }

            await loadCatalogPage();
        },
        'Desactivar producto',
        'Confirma la acción para actualizar su estado en la sucursal actual',
        'Desactivación de producto',
        'Listo para continuar',
        'Confirmar desactivación'
    );
}

    async function deactivateService(id) {
    openConfirmModal(
        '¿Deseas desactivar este servicio?',
        async () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const { response, data } = await apiFetch(`/api/services/${id}/deactivate`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            if (!response.ok) {
                alert(data.message || 'No se pudo desactivar el servicio.');
                return;
            }

            await loadCatalogPage();
        },
        'Desactivar servicio',
        'Confirma la acción para actualizar su estado',
        'Desactivación de servicio',
        'Listo para continuar',
        'Confirmar desactivación'
    );
}
    async function deleteCategory(id) {
    openConfirmModal(
        '¿Deseas eliminar definitivamente esta categoría?',
        async () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const { response, data } = await apiFetch(`/api/categories/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            if (!response.ok) {
                alert(data.errors?.category_id?.[0] || data.message || 'No se pudo eliminar la categoría.');
                return;
            }

            await loadCatalogPage();
        },
        'Eliminar categoría',
        'Esta acción eliminará la categoría del sistema',
        'Eliminación de categoría',
        'Listo para eliminar',
        'Confirmar eliminación'
    );
}
    async function deleteProduct(id) {
    openConfirmModal(
        '¿Deseas eliminar este producto de la sucursal actual? Si es su última sucursal, se eliminará completamente.',
        async () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const { response, data } = await apiFetch(`/api/products/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            if (!response.ok) {
                alert(data.errors?.product_id?.[0] || data.message || 'No se pudo eliminar el producto.');
                return;
            }

            await loadCatalogPage();
        },
        'Eliminar producto',
        'Esta acción puede afectar su disponibilidad en el catálogo',
        'Eliminación de producto',
        'Listo para eliminar',
        'Confirmar eliminación'
    );
}

    async function deleteService(id) {
    openConfirmModal(
        '¿Deseas eliminar definitivamente este servicio?',
        async () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const { response, data } = await apiFetch(`/api/services/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            if (!response.ok) {
                alert(data.message || 'No se pudo eliminar el servicio.');
                return;
            }

            await loadCatalogPage();
        },
        'Eliminar servicio',
        'Esta acción eliminará el servicio del sistema',
        'Eliminación de servicio',
        'Listo para eliminar',
        'Confirmar eliminación'
    );
}

    function openBulkModal() {
        hideError('bulkUploadErrorBox');
        document.getElementById('bulkFile').value = '';
        document.getElementById('bulkUploadModal').classList.add('show');
    }

    function closeBulkModal() {
        document.getElementById('bulkUploadModal').classList.remove('show');
    }

    async function submitBulkUpload() {
        hideError('bulkUploadErrorBox');

        const fileInput = document.getElementById('bulkFile');
        const file = fileInput.files[0];

        if (!file) {
            showError('bulkUploadErrorBox', 'Selecciona un archivo.');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const formData = new FormData();
        formData.append('file', file);

        const { response, data } = await apiFetch('/api/catalog/bulk-upload', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        });

        if (!response.ok) {
            showError(
                'bulkUploadErrorBox',
                data.errors?.file?.[0] ||
                data.message ||
                `Error ${response.status}: no se pudo subir el archivo.`
            );
            return;
        }

        const productsCreated = Number(data.products_created ?? 0);
        const productsAssigned = Number(data.products_assigned ?? 0);
        const servicesCreated = Number(data.services_created ?? 0);
        const totalProcessed = Number(data.total_processed ?? 0);

        if (totalProcessed <= 0) {
            showError(
                'bulkUploadErrorBox',
                data.errors?.file?.[0] ||
                data.message ||
                'No se importó ningún elemento.'
            );
            return;
        }

        closeBulkModal();
        await loadCatalogPage();

        alert(
            `Carga completada.\n` +
            `Productos nuevos: ${productsCreated}\n` +
            `Productos asignados a la sucursal actual: ${productsAssigned}\n` +
            `Servicios nuevos: ${servicesCreated}`
        );
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadCatalogPage();

        document.getElementById('catalogSearch').addEventListener('input', loadCatalogItems);
        document.getElementById('catalogType').addEventListener('change', loadCatalogItems);
        document.getElementById('catalogCategory').addEventListener('change', loadCatalogItems);
        document.getElementById('catalogStatus').addEventListener('change', loadCatalogItems);
        document.getElementById('itemType').addEventListener('change', toggleItemFields);
    });
</script>
@endsection