@extends('layout.dashboard_design')

@section('content')
<style>
    .customers-page {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .customers-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .customers-top h1 {
        margin: 0 0 8px;
        font-size: 32px;
        color: #0f172a;
    }

    .customers-top p {
        margin: 0;
        color: #64748b;
        font-size: 16px;
    }

    .btn-new-customer {
        background: #1d4ed8;
        color: #fff;
        border: none;
        border-radius: 14px;
        padding: 14px 22px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 10px 24px rgba(29, 78, 216, 0.20);
    }

    .btn-new-customer:hover {
        background: #1e40af;
    }

    .customers-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .summary-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 22px 24px;
    }

    .summary-label {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .summary-value {
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .summary-note {
        font-size: 14px;
        color: #64748b;
    }

    .summary-note.success {
        color: #22c55e;
    }

    .filters-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 18px 22px;
        display: grid;
        grid-template-columns: 1.8fr 0.9fr;
        gap: 14px;
    }

    .search-input,
    .filter-select {
        width: 100%;
        height: 56px;
        border: 1px solid #d1d5db;
        border-radius: 14px;
        padding: 0 16px;
        font-size: 15px;
        background: #fff;
        color: #0f172a;
        outline: none;
    }

    .search-input:focus,
    .filter-select:focus {
        border-color: #1d4ed8;
        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
    }

    .customers-table-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        overflow: hidden;
    }

    .customers-table-head {
        padding: 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .customers-table-head h2 {
        margin: 0 0 6px;
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .customers-table-head p {
        margin: 0;
        color: #64748b;
        font-size: 14px;
    }

    .customers-table-wrap {
        overflow-x: auto;
    }

    .customers-table {
        width: 100%;
        border-collapse: collapse;
    }

    .customers-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .customers-table tbody td {
        padding: 16px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
        color: #0f172a;
    }

    .customer-id {
        font-weight: 700;
        color: #1d4ed8;
        white-space: nowrap;
    }

    .customer-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 220px;
    }

    .customer-avatar {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        background: #1d4ed8;
        color: #fff;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .customer-name {
        font-weight: 700;
        color: #0f172a;
    }

    .contact-block {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 230px;
    }

    .contact-line {
        font-size: 14px;
        color: #64748b;
    }

    .amount-cell {
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
    }

    .muted-cell {
        color: #64748b;
        white-space: nowrap;
    }

    .action-dropdown {
        position: relative;
        display: inline-block;
    }

    .icon-action-btn {
        width: 42px;
        height: 42px;
        border: none;
        border-radius: 12px;
        background: #eef2ff;
        color: #1d4ed8;
        font-size: 18px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .icon-action-btn:hover {
        background: #dbeafe;
    }

    .action-menu {
        display: none;
        position: absolute;
        right: 0;
        top: 48px;
        min-width: 190px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        z-index: 50;
        overflow: hidden;
    }

    .action-menu a,
    .action-menu button {
        display: block;
        width: 100%;
        padding: 12px 14px;
        text-align: left;
        background: #fff;
        border: none;
        text-decoration: none;
        color: #0f172a;
        cursor: pointer;
        font-size: 14px;
    }

    .action-menu a:hover,
    .action-menu button:hover {
        background: #f8fafc;
    }

    .delete-action {
        color: #dc2626 !important;
    }

    .empty-row td {
        text-align: center;
        color: #64748b;
        padding: 24px 16px;
    }

    .customer-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 24px 24px 18px;
        border-bottom: 1px solid #e5e7eb;
    }

    .customer-modal-head h2 {
        margin: 0;
        font-size: 28px;
        line-height: 1.2;
        color: #0f172a;
    }

    .customer-modal-close {
        border: none;
        background: transparent;
        font-size: 22px;
        cursor: pointer;
        color: #0f172a;
    }

    .customer-modal-box {
        max-width: 760px;
    }

    .delete-modal-box {
        max-width: 520px;
    }

    .delete-modal-body {
        padding: 24px;
    }

    .delete-modal-body h3 {
        margin: 0 0 10px;
        font-size: 24px;
        color: #0f172a;
    }

    .delete-modal-body p {
        margin: 0;
        color: #64748b;
        line-height: 1.5;
    }

    .delete-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid #e5e7eb;
    }

    @media (max-width: 1200px) {
        .customers-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .filters-card {
            grid-template-columns: 1fr;
        }

        .customers-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="customers-page">
    <div class="customers-top">
        <div>
            <h1>Clientes</h1>
            <p>Gestiona la base de clientes de tu negocio.</p>
        </div>

        <button type="button" class="btn-new-customer" onclick="openCustomerModal()">
            + Nuevo cliente
        </button>
    </div>

    @if(session('success'))
        <div class="success-box">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="error-box">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="customers-summary-grid">
        <div class="summary-card">
            <div class="summary-label">Total clientes</div>
            <div class="summary-value">{{ number_format($totalCustomers) }}</div>
            <div class="summary-note success">{{ number_format($customersWithPurchases) }} con compras registradas</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Valor total</div>
            <div class="summary-value">${{ number_format($totalValue, 2) }}</div>
            <div class="summary-note">En compras acumuladas</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Gasto promedio</div>
            <div class="summary-value">${{ number_format($avgSpend, 2) }}</div>
            <div class="summary-note">Por cliente</div>
        </div>
    </div>

    <div class="filters-card">
        <input
            type="text"
            id="customerSearch"
            class="search-input"
            placeholder="Buscar por nombre, email o teléfono..."
        >

        <select id="customerFilter" class="filter-select">
            <option value="all">Todos los clientes</option>
            <option value="with_purchases">Con compras</option>
            <option value="without_purchases">Sin compras</option>
        </select>
    </div>

    <div class="customers-table-card">
        <div class="customers-table-head">
            <h2>Listado de clientes</h2>
            <p id="customersCountText">{{ number_format($totalCustomers) }} clientes registrados</p>
        </div>

        <div class="customers-table-wrap">
            <table class="customers-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Contacto</th>
                        <th>Total gastado</th>
                        <th>Compras</th>
                        <th>Última compra</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="customersTableBody">
                    @forelse($customers as $customer)
                        @php
                            $customerCode = 'CL-' . str_pad($customer->customer_id, 3, '0', STR_PAD_LEFT);
                            $nameParts = preg_split('/\s+/', trim($customer->name_customer));
                            $initials = '';

                            foreach (array_slice(array_filter($nameParts), 0, 2) as $part) {
                                $initials .= mb_strtoupper(mb_substr($part, 0, 1));
                            }

                            $searchText = mb_strtolower(
                                $customerCode . ' ' .
                                $customer->name_customer . ' ' .
                                $customer->email . ' ' .
                                $customer->phone
                            );
                        @endphp

                        <tr
                            class="customer-row"
                            data-search="{{ $searchText }}"
                            data-purchases="{{ (int) $customer->purchases_count }}"
                        >
                            <td class="customer-id">{{ $customerCode }}</td>

                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">{{ $initials ?: 'CL' }}</div>

                                    <div>
                                        <div class="customer-name">{{ $customer->name_customer }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="contact-block">
                                    <div class="contact-line">{{ $customer->email }}</div>
                                    <div class="contact-line">{{ $customer->phone }}</div>
                                </div>
                            </td>

                            <td class="amount-cell">${{ number_format((float) $customer->total_spent, 2) }}</td>
                            <td class="muted-cell">{{ (int) $customer->purchases_count }} compras</td>
                            <td class="muted-cell">
                                {{ $customer->last_purchase_at ? \Carbon\Carbon::parse($customer->last_purchase_at)->format('Y-m-d') : 'Sin compras' }}
                            </td>

                            <td>
                                <div class="action-dropdown">
                                    <button
                                        type="button"
                                        class="icon-action-btn"
                                        onclick="toggleActions({{ $customer->customer_id }})"
                                        title="Acciones"
                                    >
                                        👁️
                                    </button>

                                    <div class="action-menu" id="actions-{{ $customer->customer_id }}">
                                        <a href="{{ route('customers.history', $customer->customer_id) }}">Ver historial</a>
                                        <a href="{{ route('customers.edit', $customer->customer_id) }}">Editar cliente</a>

                                        <form id="delete-form-{{ $customer->customer_id }}" method="POST" action="{{ route('customers.delete', $customer->customer_id) }}" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>

                                        <button
                                            type="button"
                                            class="delete-action"
                                            onclick="openDeleteModal({{ $customer->customer_id }}, @js($customer->name_customer))"
                                        >
                                            Eliminar cliente
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="7">No hay clientes registrados.</td>
                        </tr>
                    @endforelse

                    <tr class="empty-row" id="filteredEmptyRow" style="display: none;">
                        <td colspan="7">No se encontraron clientes con esos filtros.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="customerModal">
    <div class="modal-box customer-modal-box">
        <div class="customer-modal-head">
            <h2>Nuevo cliente</h2>
            <button type="button" class="customer-modal-close" onclick="closeCustomerModal()">✕</button>
        </div>

        <div class="modal-body">
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf

                <div class="form-group">
                    <label>Nombre del cliente</label>
                    <input type="text" name="name_customer" class="form-input" value="{{ old('name_customer') }}" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="phone" class="form-input" value="{{ old('phone') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeCustomerModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal-overlay" id="deleteCustomerModal" style="display:none;">
    <div class="modal-box delete-modal-box">
        <div class="customer-modal-head">
            <h2>Eliminar cliente</h2>
            <button type="button" class="customer-modal-close" onclick="closeDeleteModal()">✕</button>
        </div>

        <div class="delete-modal-body">
            <h3>¿Deseas eliminar este cliente?</h3>
            <p>
                Se eliminará el registro de
                <strong id="deleteCustomerName"></strong>.
                Esta acción no se puede deshacer.
            </p>

            <div class="delete-modal-actions">
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancelar</button>
                <button type="button" class="btn-danger" onclick="confirmDeleteCustomer()">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let deleteFormId = null;

    function openCustomerModal() {
        document.getElementById('customerModal').style.display = 'flex';
    }

    function closeCustomerModal() {
        document.getElementById('customerModal').style.display = 'none';
    }

    function toggleActions(customerId) {
        const currentId = 'actions-' + customerId;
        const menu = document.getElementById(currentId);

        document.querySelectorAll('.action-menu').forEach(item => {
            if (item.id !== currentId) {
                item.style.display = 'none';
            }
        });

        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }

    function openDeleteModal(customerId, customerName) {
        deleteFormId = 'delete-form-' + customerId;
        document.getElementById('deleteCustomerName').textContent = customerName;
        document.getElementById('deleteCustomerModal').style.display = 'flex';

        document.querySelectorAll('.action-menu').forEach(item => {
            item.style.display = 'none';
        });
    }

    function closeDeleteModal() {
        deleteFormId = null;
        document.getElementById('deleteCustomerModal').style.display = 'none';
    }

    function confirmDeleteCustomer() {
        if (!deleteFormId) return;
        document.getElementById(deleteFormId).submit();
    }

    function filterCustomers() {
        const search = document.getElementById('customerSearch').value.trim().toLowerCase();
        const filter = document.getElementById('customerFilter').value;
        const rows = document.querySelectorAll('.customer-row');
        const filteredEmptyRow = document.getElementById('filteredEmptyRow');
        const customersCountText = document.getElementById('customersCountText');

        let visibleCount = 0;

        rows.forEach(row => {
            const rowSearch = row.dataset.search || '';
            const purchases = Number(row.dataset.purchases || 0);

            const matchSearch = rowSearch.includes(search);
            let matchFilter = true;

            if (filter === 'with_purchases') {
                matchFilter = purchases > 0;
            } else if (filter === 'without_purchases') {
                matchFilter = purchases === 0;
            }

            const show = matchSearch && matchFilter;
            row.style.display = show ? '' : 'none';

            if (show) {
                visibleCount++;
            }
        });

        filteredEmptyRow.style.display = visibleCount === 0 ? '' : 'none';
        customersCountText.textContent = visibleCount + ' clientes mostrados';
    }

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.action-dropdown')) {
            document.querySelectorAll('.action-menu').forEach(item => {
                item.style.display = 'none';
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('customerSearch').addEventListener('input', filterCustomers);
        document.getElementById('customerFilter').addEventListener('change', filterCustomers);

        @if($errors->any() && (old('name_customer') || old('phone') || old('email')))
        openCustomerModal();
    @endif
    });
</script>
@endsection