@extends('layout.dashboard_design')

@section('content')

<h2>Administra las preferencias de tu negocio</h2>

@if($showOnboarding)
<div class="onboarding-overlay">

    <div class="onboarding-modal">

        <!-- HEADER -->
        <div class="onboarding-header">
            <div>
                <h2>¡Bienvenido a Punto! 🎉</h2>
                <p>Completa tu negocio en menos de 2 minutos</p>
            </div>

            <span class="cerrar" onclick="cerrarOnboarding()">✖</span>
        </div>

        <!-- PROGRESO -->
        <div class="onboarding-progress">
            <div class="progress-top">
                <span>20% completado</span>
                <span>Campos sugeridos</span>
            </div>

            <div class="progress-bar">
                <div class="progress-fill" style="width:20%"></div>
            </div>
        </div>

        <!-- FORM -->
        <form method="POST" action="{{ route('onboarding.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="onboarding-body">

                <!-- LOGO BONITO -->
                <div class="form-group">
                    <label>Logo del negocio</label>

                    <label class="upload-box">
                        <input type="file" name="logo">
                        <div>
                            ⬆️
                            <p>Haz clic para subir tu logo</p>
                            <small>PNG, JPG hasta 2MB</small>
                        </div>
                    </label>
                </div>

                <!-- DIRECCIÓN -->
                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" name="address" class="form-input" placeholder="Ej. Av. Reforma 123">
                </div>

                <!-- MONEDA -->
                <div class="form-group">
                    <label>Moneda</label>
                    <select name="currency" class="form-input">
                        <option value="">Selecciona</option>
                        <option value="MXN">MXN - Peso Mexicano</option>
                    </select>
                </div>

                <!-- HORARIOS -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Hora de apertura</label>
                        <input type="time" name="opening_time" class="form-input">
                    </div>

                    <div class="form-group">
                        <label>Hora de cierre</label>
                        <input type="time" name="closing_time" class="form-input">
                    </div>
                </div>

            </div>

            <!-- FOOTER BONITO -->
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



<div class="cards">

    <div class="card">
        <h4>Ventas del día</h4>
        <p><strong>$</strong> {{ number_format($ventasDia ?? 0, 2) }}</p>
    </div>

    <div class="card">
        <h4>Productos</h4>
        <p>{{ $productos ?? 0 }}</p>
    </div>

    <div class="card">
        <h4>Clientes</h4>
        <p>{{ $clientes ?? 0 }}</p>
    </div>

    <div class="card">
        <h4>Pedidos</h4>
        <p>{{ $pedidos ?? 0 }}</p>
    </div>

</div>

<div class="charts">

    <div class="chart-card">
        <h3>Ventas de la semana</h3>
        <div class="chart-container">
            <canvas id="ventasChart"></canvas>
        </div>
    </div>

    <!-- 💳 PAGOS CON INFO -->
    <div class="chart-card" style="display:flex; gap:20px; align-items:center;">
        
        <!-- GRÁFICA -->
        <div style="width:50%;">
            <canvas id="pagosChart"></canvas>
        </div>


    </div>

</div>

<div class="table-card">

        <h3>Registro de Ventas</h3>
        <form method="GET" action="{{ route('dashboard') }}" class="filters">

    <input 
        type="text" 
        name="search" 
        placeholder="Buscar por cliente o metodo..."
        value="{{ request('search') }}"
    >

    <input 
        type="date" 
        name="date" 
        value="{{ request('date') }}"
    >

    <select name="status">
        <option value="">Todos los estados</option>
        <option value="PAGADO" {{ request('status') == 'PAGADO' ? 'selected' : '' }}>Pagado</option>
        <option value="PENDIENTE" {{ request('status') == 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
    </select>

    <button type="submit">Buscar</button>

</form>

    <table class="sales-table">

        <!-- HEADER -->
        <thead>
            <tr>
                
                <th>Cliente</th>
                <th>Monto</th>
                <th>Método</th>
                <th>Estado</th>
                <th>Fecha</th>
            </tr>
        </thead>

        <!-- BODY -->
        <tbody>
            @forelse($ventas as $venta)
            <tr>
               

                <td>{{ $venta->customer->name_customer ?? 'Sin cliente' }}</td>

                <td><strong>$</strong> {{ number_format($venta->total, 2) }}</td>

                 <td> {{ $venta->payment->payment_method ?? 'N/A' }}</td>
                 

                <td>{{ $venta->payment->status_payment ?? 'N/A' }}</td>

                <td>{{ \Carbon\Carbon::parse($venta->date_time)->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5">No hay ventas</td>
            </tr>
            @endforelse
        </tbody>

    </table>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>



<script>
const ventasData = @json($ventasSemana);

new Chart(document.getElementById('ventasChart'), {
    type: 'line',
    data: {
        labels: ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'],
        datasets: [{
            label: 'Ventas',
            data: ventasData,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.15)',
            fill: true,
            tension: 0.4
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false
    }
});

/* ===== PAGOS ===== */

const metodosData = @json($metodosPago);

const valores = [
    metodosData.efectivo,
    metodosData.tarjeta,
    metodosData.transferencia
];

const total = valores.reduce((a, b) => a + b, 0);

new Chart(document.getElementById('pagosChart'), {
    type: 'doughnut',
    data: {
        labels: ['Efectivo','Tarjeta','Transferencia'],
        datasets: [{
            data: valores,
            backgroundColor: ['#3b82f6','#22c55e','#f59e0b'],
            borderWidth: 0
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        cutout: '65%',
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let value = context.raw;
                        let percentage = total ? ((value / total) * 100).toFixed(1) : 0;
                        return `${context.label}: ${value} (${percentage}%)`;
                    }
                }
            }
        }
    }
});
</script>

</script>

@endsection