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
        <p>${{ number_format($ventasDia ?? 0) }}</p>
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

    <div class="chart-card">
        <h3>Métodos de pago</h3>
        <div class="chart-container">
            <canvas id="pagosChart"></canvas>
        </div>
    </div>

</div>


<div class="table-card">

        <h3>Registro de Ventas</h3>

    <table class="sales-table">

        <!-- HEADER -->
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Monto</th>
                <th>Método</th>
                <th>Fecha</th>
            </tr>
        </thead>

        <!-- BODY -->
        <tbody>
            @forelse($ventas as $venta)
            <tr>
                <td>{{ $venta->sale_id }}</td>

                <td>{{ $venta->customer_idfk }}</td>

                <td>${{ number_format($venta->total) }}</td>

                <td>{{ $venta->status_sale }}</td>

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

/* ===== VENTAS ===== */
new Chart(document.getElementById('ventasChart'), {
    type: 'line',
    data: {
        labels: ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'],
        datasets: [{
            label: 'Ventas',
            data: [2500,3500,2800,4200,3900,5200,4500],
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
new Chart(document.getElementById('pagosChart'), {
    type: 'doughnut',
    data: {
        labels: ['Efectivo','Tarjeta','Transferencia'],
        datasets: [{
            data: [40,35,25],
            backgroundColor: ['#3b82f6','#22c55e','#f59e0b'],
            borderWidth: 0
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        cutout: '65%'
    }
});

</script>

@endsection