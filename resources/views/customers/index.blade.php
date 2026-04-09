@extends('layout.dashboard_design')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/customers/index.css') }}">
@endpush

@section('content')

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