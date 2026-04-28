@extends('layout.dashboard_design')

@section('content')
@php
    $companyId = auth()->user()->company_idfk;
@endphp

<div class="dashboard-page">
    <div class="settings-header">
        <h1>Dashboard</h1>
        <p>Administra las preferencias de tu negocio.</p>
    </div>

    @if($showOnboarding)
        <div class="onboarding-overlay">
            <div class="onboarding-modal">
                <div class="onboarding-header">
                    <div>
                        <h2>¡Bienvenido a Punto! 🎉</h2>
                        <p>Completa tu negocio en menos de 2 minutos</p>
                    </div>

                    <span class="cerrar" onclick="cerrarOnboarding()">✖</span>
                </div>

                <div class="onboarding-progress">
                    <div class="progress-top">
                        <span id="progressText">0% completado</span>
                        <span id="progressFields">6 campos sugeridos</span>
                    </div>

                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                </div>

                <form method="POST" action="{{ route('onboarding.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="onboarding-body">
                        <div class="form-group">
                            <label>Logo del negocio</label>

                            <label for="logoInput" class="dashboard-upload-box">
                                <input type="file" name="logo" id="logoInput">
                                <div>
                                    ⬆️
                                    <p>Haz clic para subir tu logo</p>
                                    <small>PNG, JPG hasta 2MB</small>
                                </div>
                            </label>
                        </div>

                        <div class="form-group">
                            <label>Dirección</label>
                            <input
                                type="text"
                                name="address"
                                id="addressInput"
                                class="form-input"
                                placeholder="Ej. Av. Reforma 123, Col. Centro"
                            >
                        </div>

                        <div class="form-group">
                            <label>Moneda</label>
                            <select name="currency" id="currencyInput" class="form-input">
                                <option value="">Selecciona una moneda</option>
                                <option value="MXN">MXN - Peso Mexicano</option>
                                <option value="USD">USD - Dólar</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Hora de apertura</label>
                                <input type="time" name="opening_time" id="openingInput" class="form-input">
                            </div>

                            <div class="form-group">
                                <label>Hora de cierre</label>
                                <input type="time" name="closing_time" id="closingInput" class="form-input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Métodos de pago aceptados</label>

                            <div class="payment-grid">
                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Efectivo" class="payment-method">
                                    <span>Efectivo</span>
                                </label>

                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Tarjeta" class="payment-method">
                                    <span>Tarjeta</span>
                                </label>

                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Transferencia" class="payment-method">
                                    <span>Transferencia</span>
                                </label>

                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Cheque" class="payment-method">
                                    <span>Cheque</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="onboarding-footer">
                        <button type="submit" name="skip" value="1" class="btn-secondary">
                            Omitir por ahora
                        </button>

                        <button type="submit" class="btn-primary">
                            Guardar y continuar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="dashboard-summary-grid">
        <div class="dashboard-summary-card">
            <h3>Ventas del día</h3>
            <p>{{ \App\Support\CompanyPreference::formatMoneyForCompany($companyId, $ventasDia ?? 0) }}</p>
        </div>

        <div class="dashboard-summary-card">
            <h3>Productos</h3>
            <p>{{ $productos ?? 0 }}</p>
        </div>

        <div class="dashboard-summary-card">
            <h3>Clientes</h3>
            <p>{{ $clientes ?? 0 }}</p>
        </div>

        <div class="dashboard-summary-card">
            <h3>Pedidos</h3>
            <p>{{ $pedidos ?? 0 }}</p>
        </div>
    </div>

    <div class="dashboard-charts-grid">
        <div class="dashboard-chart-card">
            <h3>Ventas de la semana</h3>
            <div class="dashboard-chart-container">
                <canvas id="ventasChart"></canvas>
            </div>
        </div>

        <div class="dashboard-chart-card">
            <h3>Métodos de pago</h3>
            <div class="dashboard-chart-container">
                <canvas id="pagosChart"></canvas>
            </div>
        </div>
    </div>

    <div class="dashboard-table-card">
        <h3>Registro de Ventas</h3>

        <div style="overflow-x:auto;">
            <table class="dashboard-sales-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($ventas as $venta)
                        <tr>
                            <td>{{ $venta->customer->name_customer ?? 'Sin cliente' }}</td>
                            <td>{{ \App\Support\CompanyPreference::formatMoneyForCompany($companyId, $venta->total ?? 0) }}</td>
                            <td>{{ $venta->payment->payment_method ?? 'N/A' }}</td>
                            <td>{{ $venta->payment->status_payment ?? 'N/A' }}</td>
                            <td>{{ \App\Support\CompanyPreference::formatDateForCompany($companyId, $venta->date_time) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;">No hay ventas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ventasChartEl = document.getElementById('ventasChart');
    if (ventasChartEl) {
        new Chart(ventasChartEl, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Ventas',
                    data: [2500, 3500, 2800, 4200, 3900, 5200, 4500],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.15)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    const pagosChartEl = document.getElementById('pagosChart');
    if (pagosChartEl) {
        new Chart(pagosChartEl, {
            type: 'doughnut',
            data: {
                labels: ['Efectivo', 'Tarjeta', 'Transferencia'],
                datasets: [{
                    data: [40, 35, 25],
                    backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%'
            }
        });
    }
</script>
@endsection