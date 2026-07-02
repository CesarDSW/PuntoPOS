@extends('layout.dashboard_design')

@section('content')
@php
    $companyId = auth()->user()->company_idfk;
@endphp

{{-- ================= ONBOARDING ================= --}}
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

                            <label for="logoInput" class="dashboard-upload-box" id="logoUploadBox">
                                <input 
                                    type="file" 
                                    name="logo" 
                                    id="logoInput"
                                    accept="image/png,image/jpeg,image/jpg,image/webp">

                                <div class="upload-placeholder" id="uploadPlaceholder">
                                    <div class="upload-icon">⬆️</div>
                                    <p>Haz clic para subir tu logo</p>
                                    <small>PNG, JPG hasta 2MB</small>
                                </div>

                                <div class="logo-preview-wrapper" id="logoPreviewWrapper" style="display: none;">
                                    <img id="logoPreview" class="logo-preview-img" alt="Vista previa del logo">
                                    <div class="logo-file-meta">
                                        <strong id="logoFileName">archivo.png</strong>
                                        <small>Imagen seleccionada correctamente</small>
                                    </div>     
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

    {{-- ================= BANNER ================= --}}
    @php
        $mostrarTrial = $subscription && (bool) $subscription->status_subscription;
    @endphp

    @if($mostrarTrial)
        <div class="trial-banner">
            <div class="trial-left">
                <div class="trial-icon">
                    ✨
                </div>

                <div>
                    <strong>
                        Estás usando Punto en prueba gratuita
                    </strong>

                    <p>
                        Disfruta todas las funcionalidades premium sin restricciones
                    </p>
                </div>
            </div>

            <div class="trial-right">
                <span class="trial-days">
                    {{ $diasRestantes }} restantes
                </span>
                <a
                    href="#"
                    onclick="abrirPlanes()"
                    class="btn-banner-planes">
                    Ver planes →
                </a>

                <span
                    class="trial-close"
                    onclick="cerrarBanner()">
                    ✖
                </span>
            </div>
        </div>
    @endif

    {{-- ================= MODAL (CORRECTO) ================= --}}
    <div id="modalPlanes" class="planes-modal">
        <div class="planes-content">
            <!-- HEADER PRO -->
            <div class="planes-header">
                <div class="header-text">
                    <h2>Elige tu plan perfecto</h2>
                    <p>Desbloquea todo el potencial de tu negocio 🚀</p>
                </div>
                <span onclick="cerrarPlanes()" class="close-btn">✖</span>
            </div>

            <!-- TOGGLE PRO -->
            <div class="billing-toggle">
                <div class="toggle-wrapper">
                    <button class="toggle-btn active" onclick="cambiarPlan('mensual')">
                        Mensual
                    </button>

                    <button class="toggle-btn" onclick="cambiarPlan('anual')">
                        Anual
                    </button>
                </div>
            </div>
  
        {{-- ================= PLANES ================= --}}
        <div class="plans-grid">
            <!-- BASICO -->
            <div class="plan-card">
                <div class="plan-icon icon-basic">
                    ✨
                </div>
                
                <h3>Básico</h3>
                <small>Perfecto para empezar</small>

                <div class="price">
                    <span class="precio" data-mensual="549" data-anual="458">$549</span>
                    <span>MXN/mes</span>
                </div>

                <p class="annual-text">Facturado anualmente: $5,490 MXN</p>
                <p class="save-text">Ahoras: $1,098 MXN</p>

                <hr class="divider">

                <ul class="features">
                    <li>✔ Hasta 500 productos</li>
                    <li>✔ 1 usuario</li>
                    <li>✔ Ventas ilimitadas</li>
                    <li>✔ Reportes básicos</li>
                    <li>✔ Soporte por email</li>
                    <li>✔ App móvil</li>
                </ul>

                <button 
                    class="btn-plan"
                    onclick="seleccionarPlan(this, 'basico')">
                    Continuar con este plan
                </button>
            </div>

            <!-- PRO -->
            <div class="plan-card popular">
                <div class="popular-badge">
                    ⭐ Más popular
                </div>

                <div class="plan-icon icon-pro">
                    📈
                </div>

                <h3>Pro</h3>
                <small>Para negocios en crecimiento</small>

                <div class="price">
                    <span class="precio" data-mensual="899" data-anual="749">$899</span>
                    <span>MXN/mes</span>
                </div>

                <p class="annual-text">Facturado anualmente: $8,990 MXN</p>
                <p class="save-text">Ahoras: $1,798 MXN</p>

                <hr class="divider">

                <ul class="features">
                    <li>✔ Productos ilimitados</li>
                    <li>✔ Hasta 5 usuarios</li>
                    <li>✔ Ventas ilimitadas</li>
                    <li>✔ Reportes avanzados</li>
                    <li>✔ Soporte prioritario</li>
                    <li>✔ App móvil</li>
                    <li>✔ Múltiples sucursales</li>
                    <li>✔ Inventario avanzado</li>
                    <li>✔ Integraciones API</li>
                </ul>

                <button 
                    class="btn-plan"
                    onclick="seleccionarPlan(this, 'pro')">
                    Continuar con este plan
                </button>
            </div>

            <!-- NEGOCIO -->
            <div class="plan-card">
                <div class="plan-icon icon-business">
                    🚀
                </div>
                
                <h3>Negocio</h3>
                <small>Para operaciones grandes</small>

                <div class="price">
                    <span class="precio" data-mensual="1499" data-anual="1249">$1499</span>
                    <span>MXN/mes</span>
                </div>

                <p class="annual-text">Facturado anualmente: $14,990 MXN</p>
                <p class="save-text">Ahoras: $2,998 MXN</p>

                <hr class="divider">

                <ul class="features">
                    <li>✔ Todo lo del plan Pro</li>
                    <li>✔ Usuarios ilimitados</li>
                    <li>✔ Sucursales ilimitadas</li>
                    <li>✔ Reportes personalizados</li>
                    <li>✔ Soporte 24/7</li>
                    <li>✔ Gestor de cuenta dedicado</li>
                    <li>✔ Capacitación incluida</li>
                    <li>✔ Integraciones premium</li>
                    <li>✔ White label</li>
                </ul>

                <button 
                    class="btn-plan"
                    onclick="seleccionarPlan(this, 'negocio')">
                    Continuar con este plan
                </button>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="plans-footer">
                <p class="footer-title">Todos los planes incluyen:</p>
                <div class="footer-items">
                    <div>🛡️ Pago seguro</div>
                    <div>⚡ Activación inmediata</div>
                    <div>👤 Sin permanencia</div>
                    <div>📦 Cancela cuando quieras</div>
                </div>
            </div>
        </div>
    </div>

{{-- ================= DASHBOARD ================= --}}
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

    function abrirPlanes() {
        document.getElementById('modalPlanes').classList.add('show');
    }

    function cerrarPlanes() {
        document.getElementById('modalPlanes').classList.remove('show');
    }

    function cambiarPlan(tipo) {
        // CAMBIAR BOTON ACTIVO
        document.querySelectorAll('.toggle-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        // ACTIVAR EL BOTON CORRECTO
        if (tipo === 'mensual') {
            document.querySelectorAll('.toggle-btn')[0].classList.add('active');
        } else {
            document.querySelectorAll('.toggle-btn')[1].classList.add('active');
        }
        // CAMBIAR PRECIOS
        document.querySelectorAll('.precio').forEach(el => {
            el.innerText = '$' + el.getAttribute('data-' + tipo);
        });
    }

    function seleccionarPlan(boton, plan) {
        // quitar selección anterior
        document.querySelectorAll('.plan-card').forEach(card => {
            card.classList.remove('popular');
        });
        // seleccionar nueva tarjeta
        boton.closest('.plan-card').classList.add('popular');
        // redireccionar al pago
        window.location.href = '/checkout/' + plan;
    }

    // =========================================
    // 🔥 CERRAR BANNER
    // =========================================
    function cerrarBanner()
    {
        const banner = document.querySelector('.trial-banner');
        banner.style.display = 'none';
    }

    // =========================================
    // PREVIEW DE LOGO EN ONBOARDING
    // =========================================
    const logoInput = document.getElementById('logoInput');
    const logoPreview = document.getElementById('logoPreview');
    const logoPreviewWrapper = document.getElementById('logoPreviewWrapper');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const logoFileName = document.getElementById('logoFileName');
    const logoUploadBox = document.getElementById('logoUploadBox');

    if (
        logoInput &&
        logoPreview &&
        logoPreviewWrapper &&
        uploadPlaceholder &&
        logoFileName &&
        logoUploadBox
    ) {
        logoInput.addEventListener('change', function (event) {
            const file = event.target.files[0];

            if (!file) {
                logoPreviewWrapper.style.display = 'none';
                uploadPlaceholder.style.display = 'flex';
                logoUploadBox.classList.remove('has-image');
                logoPreview.src = '';
                logoFileName.textContent = '';
                return;
            }

            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];

            if (!allowedTypes.includes(file.type)) {
                alert('Solo se permiten imágenes PNG, JPG, JPEG o WEBP.');
                logoInput.value = '';
                logoPreviewWrapper.style.display = 'none';
                uploadPlaceholder.style.display = 'flex';
                logoUploadBox.classList.remove('has-image');
                logoPreview.src = '';
                logoFileName.textContent = '';
                return;
            }

            const maxSize = 2 * 1024 * 1024; // 2MB
            if (file.size > maxSize) {
                alert('La imagen no debe superar los 2MB.');
                logoInput.value = '';
                logoPreviewWrapper.style.display = 'none';
                uploadPlaceholder.style.display = 'flex';
                logoUploadBox.classList.remove('has-image');
                logoPreview.src = '';
                logoFileName.textContent = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                logoPreview.src = e.target.result;
                logoFileName.textContent = file.name;
                uploadPlaceholder.style.display = 'none';
                logoPreviewWrapper.style.display = 'flex';
                logoUploadBox.classList.add('has-image');
            };

            reader.readAsDataURL(file);
        });
    }
</script>

@endsection