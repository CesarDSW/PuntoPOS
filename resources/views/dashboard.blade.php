@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/layout/dashboard.css') }}">
@endpush

@section('content')
<div class="dashboard-wrap">
    <div class="dashboard-header">
        <div>
            <h1 style="font-size:32px; margin-bottom:8px;">Dashboard</h1>
            <p class="text-muted">Resumen general de tu negocio</p>

            <div class="dashboard-branch-row">
                <span class="branch-pill">{{ $currentBranchName }}</span>
            </div>
        </div>

        <div class="dashboard-actions">
            <a href="{{ route('sales.pos') }}" class="btn btn-dark">Abrir POS</a>
            <a href="{{ route('sales.pos') }}" class="btn btn-primary">Nueva venta</a>
        </div>
    </div>

    <div class="summary-grid summary-grid-4">
        <div class="summary-card">
            <div class="summary-card-top">
                <span class="trend-badge {{ $incomeChange >= 0 ? 'trend-up' : 'trend-down' }}">
                    {{ number_format(abs($incomeChange), 1) }}%
                </span>
            </div>
            <div class="summary-label">Ingresos del día</div>
            <div class="summary-value">${{ number_format($incomeToday, 0) }}</div>
            <div class="summary-note">vs. mismo día de la semana pasada</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <span class="trend-badge {{ $salesChange >= 0 ? 'trend-up' : 'trend-down' }}">
                    {{ number_format(abs($salesChange), 1) }}%
                </span>
            </div>
            <div class="summary-label">Ventas realizadas</div>
            <div class="summary-value">{{ number_format($salesToday) }}</div>
            <div class="summary-note">vs. mismo día de la semana pasada</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <span class="trend-badge {{ $activeCustomersChange >= 0 ? 'trend-up' : 'trend-down' }}">
                    {{ number_format(abs($activeCustomersChange), 1) }}%
                </span>
            </div>
            <div class="summary-label">Clientes activos</div>
            <div class="summary-value">{{ number_format($activeCustomers) }}</div>
            <div class="summary-note">últimos 30 días vs. 30 días previos</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <span class="trend-badge {{ $transactionsChange >= 0 ? 'trend-up' : 'trend-down' }}">
                    {{ number_format(abs($transactionsChange), 1) }}%
                </span>
            </div>
            <div class="summary-label">Transacciones</div>
            <div class="summary-value">{{ number_format($transactionsToday) }}</div>
            <div class="summary-note">vs. mismo día de la semana pasada</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <div class="card-head">
                <div>
                    <div class="card-title">Ventas de la semana</div>
                    <div class="card-subtitle">Comparativa diaria en pesos</div>
                </div>
            </div>

            <div class="chart-box">
                <canvas id="weeklySalesChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div>
                    <div class="card-title">Métodos de pago</div>
                    <div class="card-subtitle">Distribución semanal</div>
                </div>
            </div>

            <div class="payment-panel">
                <div class="donut-wrap">
                    <canvas id="paymentMethodsChart"></canvas>
                </div>

                <div class="payment-list">
                    <div class="payment-item">
                        <div class="payment-left">
                            <span class="payment-dot payment-dot-card"></span>
                            <span>Tarjeta</span>
                        </div>
                        <strong>{{ $paymentBreakdown['Tarjeta']['percentage'] }}%</strong>
                    </div>

                    <div class="payment-item">
                        <div class="payment-left">
                            <span class="payment-dot payment-dot-cash"></span>
                            <span>Efectivo</span>
                        </div>
                        <strong>{{ $paymentBreakdown['Efectivo']['percentage'] }}%</strong>
                    </div>

                    <div class="payment-item">
                        <div class="payment-left">
                            <span class="payment-dot payment-dot-transfer"></span>
                            <span>Transferencia</span>
                        </div>
                        <strong>{{ $paymentBreakdown['Transferencia']['percentage'] }}%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">Transacciones recientes</div>
                <div class="card-subtitle">Últimas operaciones realizadas</div>
            </div>
        </div>

        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th>Hora</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentTransactions as $transaction)
                        @php
                            $status = strtoupper((string) $transaction->status_payment);
                            $badgeClass = 'badge badge-blue';
                            $statusText = ucfirst(strtolower($transaction->status_payment));

                            if (str_contains($status, 'PEND')) {
                                $badgeClass = 'badge badge-yellow';
                            } elseif (str_contains($status, 'CANCEL') || str_contains($status, 'RECHAZ')) {
                                $badgeClass = 'badge badge-red';
                            }
                        @endphp
                        <tr>
                            <td style="font-weight:600;">{{ $transaction->name_customer }}</td>
                            <td style="font-weight:700;">${{ number_format((float) $transaction->display_amount, 2) }}</td>
                            <td>{{ $transaction->payment_method }}</td>
                            <td><span class="{{ $badgeClass }}">{{ $statusText }}</span></td>
                            <td>{{ $transaction->hour_display }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-box">No hay transacciones recientes para mostrar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($showOnboarding)
    <div class="onboarding-overlay">
        <div class="onboarding-modal">
            <div class="onboarding-header">
                <h2>¡Bienvenido a Punto!</h2>
                <p>Completa tu negocio en menos de 2 minutos.</p>        
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
                        <input type="file" name="logo" id="logoInput" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="address" id="addressInput" class="form-input" placeholder="Ej. Av. Reforma 123, Col. Centro">
                    </div>
                    
                    <div class="form-group">
                        <label>Moneda</label>
                        <select name="currency" id="currencyInput" class="form-input">
                            <option value="">Selecciona una moneda</option>    
                            <option value="MXN">MXN - Peso Mexicano</option>
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
                        <div class="payment-grid-onboarding">
                            <label class="payment-option-onboarding">
                                <input type="checkbox" name="payment_methods[]" value="Efectivo" class="payment-method">
                                <span>Efectivo</span>
                            </label>
                            
                            <label class="payment-option-onboarding">
                                <input type="checkbox" name="payment_methods[]" value="Tarjeta" class="payment-method">
                                <span>Tarjeta</span>
                            </label>
                        
                            <label class="payment-option-onboarding">
                                <input type="checkbox" name="payment_methods[]" value="Transferencia" class="payment-method">
                                <span>Transferencia</span>
                            </label>
                            
                            <label class="payment-option-onboarding">
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
                
                    <button type="submit" class="btn-primary-onboarding">
                        Guardar y continuar
                    </button>
                </div>    
            </form>
        </div>
    </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const weeklySalesLabels = @json($weeklySalesLabels);
    const weeklySalesData = @json($weeklySalesData);
    const paymentLabels = @json($paymentChartLabels);
    const paymentData = @json($paymentChartData);

    document.addEventListener('DOMContentLoaded', function () {
        const weeklySalesCtx = document.getElementById('weeklySalesChart');
        const paymentMethodsCtx = document.getElementById('paymentMethodsChart');

        if (weeklySalesCtx) {
            new Chart(weeklySalesCtx, {
                type: 'line',
                data: {
                    labels: weeklySalesLabels,
                    datasets: [{
                        data: weeklySalesData,
                        borderColor: '#1d4ed8',
                        backgroundColor: 'rgba(29, 78, 216, 0.08)',
                        fill: false,
                        tension: 0.3,
                        pointRadius: 4,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#1d4ed8',
                        pointBorderColor: '#1d4ed8',
                        borderWidth: 2.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { color: '#e5e7eb' },
                            ticks: { color: '#64748b' }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#e5e7eb' },
                            ticks: {
                                color: '#64748b',
                                callback: function(value) {
                                    return '$' + Number(value).toLocaleString('es-MX');
                                }
                            }
                        }
                    }
                }
            });
        }

        if (paymentMethodsCtx) {
            new Chart(paymentMethodsCtx, {
                type: 'doughnut',
                data: {
                    labels: paymentLabels,
                    datasets: [{
                        data: paymentData,
                        backgroundColor: ['#1d4ed8', '#22c55e', '#6366f1'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        const inputs = [
            document.getElementById('logoInput'),
            document.getElementById('addressInput'),
            document.getElementById('currencyInput'),
            document.getElementById('openingInput'),
            document.getElementById('closingInput'),
            ...document.querySelectorAll('.payment-method')
        ];

        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');

        function updateProgress() {
            if (!progressFill || !progressText) return;

            let completed = 0;

            if (document.getElementById('logoInput')?.files?.length > 0) completed++;
            if (document.getElementById('addressInput')?.value.trim() !== '') completed++;
            if (document.getElementById('currencyInput')?.value !== '') completed++;
            if (document.getElementById('openingInput')?.value !== '') completed++;
            if (document.getElementById('closingInput')?.value !== '') completed++;

            const paymentChecked = Array.from(document.querySelectorAll('.payment-method')).some(el => el.checked);
            if (paymentChecked) completed++;

            const percent = Math.round((completed / 6) * 100);
            progressFill.style.width = percent + '%';
            progressText.textContent = percent + '% completado';
        }

        inputs.forEach(input => {
            if (!input) return;
            input.addEventListener('change', updateProgress);
            input.addEventListener('input', updateProgress);
        });

        updateProgress();
    });
</script>
@endsection