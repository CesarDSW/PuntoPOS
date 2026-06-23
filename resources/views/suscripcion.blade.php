@extends('layout.suscripcion')

@section('content')

<style>

body{
    background:
    radial-gradient(circle at top left,#2563eb 0%,transparent 30%),
    radial-gradient(circle at bottom right,#7c3aed 0%,transparent 30%),
    #0f172a;

    font-family:Arial, Helvetica, sans-serif;
    margin:0;
    padding:0;
}

/* =========================================
🔥 MODAL
========================================= */

.planes-modal{

    width:100%;
    min-height:100vh;

    display:flex;

    justify-content:center;

    align-items:center;

    padding:40px;

    box-sizing:border-box;

}

/* =========================================
🔥 CONTENIDO
========================================= */

.planes-content{

    width:100%;
    max-width:1250px;

    background:rgba(255,255,255,0.95);

    backdrop-filter:blur(12px);

    border-radius:35px;

    overflow:hidden;

    box-shadow:
    0 20px 60px rgba(0,0,0,0.35);

    animation:fadeIn 0.4s ease;

}

/* =========================================
🔥 HEADER
========================================= */

.planes-header{

    background:
    linear-gradient(
        135deg,
        #2563eb,
        #7c3aed
    );

    color:white;

    padding:45px;

    text-align:center;

}

.planes-header h2{

    font-size:42px;

    margin:0;

}

.planes-header p{

    margin-top:10px;

    font-size:18px;

    opacity:0.95;

}

/* =========================================
🔥 SUSCRIPCIÓN ACTIVA
========================================= */

.active-subscription-box{

    margin:35px 30px 0;

    padding:25px;

    border-radius:25px;

    background:
    linear-gradient(
        135deg,
        rgba(22,163,74,0.12),
        rgba(37,99,235,0.10)
    );

    border:1px solid rgba(22,163,74,0.35);

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:20px;

    flex-wrap:wrap;

}

.active-subscription-info h3{

    margin:0 0 8px;

    color:#0f172a;

    font-size:24px;

}

.active-subscription-info p{

    margin:0;

    color:#475569;

    font-size:16px;

}

.active-subscription-actions{

    display:flex;

    align-items:center;

    gap:12px;

    flex-wrap:wrap;

}

.btn-portal,
.btn-dashboard{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    min-height:46px;

    padding:0 20px;

    border-radius:14px;

    text-decoration:none;

    font-weight:bold;

    transition:0.3s;

    border:none;

    cursor:pointer;

}

.btn-portal{

    background:
    linear-gradient(
        135deg,
        #2563eb,
        #1d4ed8
    );

    color:white;

}

.btn-dashboard{

    background:white;

    color:#2563eb;

    border:1px solid #bfdbfe;

}

.btn-portal:hover,
.btn-dashboard:hover{

    transform:translateY(-2px);

    box-shadow:
    0 10px 22px rgba(37,99,235,0.20);

}

/* =========================================
🔥 TOGGLE
========================================= */

.billing-toggle{

    display:flex;

    justify-content:center;

    margin:35px 0;

}

.toggle-wrapper{

    background:#e2e8f0;

    padding:6px;

    border-radius:50px;

    display:flex;

    gap:5px;

}

.toggle-btn{

    border:none;

    padding:12px 30px;

    border-radius:50px;

    cursor:pointer;

    font-weight:bold;

    background:transparent;

    transition:0.3s;

    font-size:15px;

}

.toggle-btn.active{

    background:#2563eb;

    color:white;

    box-shadow:0 5px 15px rgba(37,99,235,0.4);

}

/* =========================================
🔥 GRID
========================================= */

.plans-grid{

    display:grid;

    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));

    gap:30px;

    padding:30px;

}

/* =========================================
🔥 CARDS
========================================= */

.plan-card{

    background:white;

    border-radius:30px;

    padding:35px;

    position:relative;

    transition:0.35s ease;

    border:1px solid #e2e8f0;

    overflow:hidden;

}

.plan-card:hover{

    transform:
    translateY(-10px)
    scale(1.02);

    box-shadow:
    0 20px 40px rgba(37,99,235,0.18);

}

.plan-card.popular{

    border:3px solid #2563eb;

}

/* =========================================
🔥 BADGE
========================================= */

.popular-badge{

    position:absolute;

    top:15px;

    left:50%;

    transform:translateX(-50%);

    background:#2563eb;

    color:white;

    padding:8px 18px;

    border-radius:30px;

    font-size:13px;

    font-weight:bold;

    z-index:10;

}

/* =========================================
🔥 ICONOS
========================================= */

.plan-icon{

    width:50px;

    height:50px;

    border-radius:15px;

    display:flex;

    justify-content:center;

    align-items:center;

    font-size:22px;

    margin-bottom:20px;

    color:white;

}

.icon-basic{
    background:#6b7280;
}

.icon-pro{
    background:#2563eb;
}

.icon-business{
    background:#9333ea;
}

/* =========================================
🔥 TITULOS
========================================= */

.plan-card h3{

    font-size:35px;

    margin:10px 0;

    color:#0f172a;

}

.plan-card small{

    color:#64748b;

    font-size:15px;

}

/* =========================================
🔥 PRECIOS
========================================= */

.price{

    margin-top:20px;

}

.precio{

    font-size:48px;

    font-weight:bold;

    color:#2563eb;

}

.price span:last-child{

    font-size:20px;

    color:#64748b;

}

/* =========================================
🔥 TEXTOS
========================================= */

.annual-text{

    margin-top:18px;

    color:#64748b;

    font-size:15px;

}

.save-text{

    color:#16a34a;

    font-weight:bold;

    font-size:15px;

}

/* =========================================
🔥 DIVIDER
========================================= */

.divider{

    margin:20px 0;

    border:none;

    border-top:1px solid #e2e8f0;

}

/* =========================================
🔥 FEATURES
========================================= */

.features{

    margin-top:25px;

    padding-left:20px;

}

.features li{

    margin-bottom:14px;

    color:#1e293b;

    font-size:17px;

}

/* =========================================
🔥 BOTÓN
========================================= */

.btn-plan{

    width:100%;

    display:flex;

    justify-content:center;

    align-items:center;

    margin-top:30px;

    background:
    linear-gradient(
        135deg,
        #2563eb,
        #1d4ed8
    );

    color:white;

    text-align:center;

    padding:15px;

    border-radius:18px;

    text-decoration:none;

    font-weight:bold;

    transition:0.3s;

    box-sizing:border-box;

    font-size:15px;

    border:none;

    cursor:pointer;

}

.btn-plan:hover{

    transform:scale(1.02);

    box-shadow:
    0 10px 25px rgba(37,99,235,0.25);

}

/* =========================================
🔥 FOOTER
========================================= */

.plans-footer{

    margin-top:10px;

    text-align:center;

    padding:35px;

    border-top:1px solid #e2e8f0;

}

.footer-title{

    font-weight:bold;

    margin-bottom:25px;

    font-size:18px;

    color:#475569;

}

.footer-items{

    display:flex;

    justify-content:center;

    gap:20px;

    flex-wrap:wrap;

}

.footer-items div{

    display:flex;

    align-items:center;

    gap:10px;

    background:white;

    padding:15px 25px;

    border-radius:15px;

    box-shadow:0 5px 15px rgba(0,0,0,0.05);

    font-weight:500;

    color:#334155;

}

/* =========================================
🔥 RESPONSIVE
========================================= */

@media(max-width:768px){

    .planes-modal{

        padding:20px;

    }

    .planes-header{

        padding:35px 25px;

    }

    .planes-header h2{

        font-size:32px;

    }

    .active-subscription-box{

        margin:25px 20px 0;

        align-items:flex-start;

    }

    .active-subscription-actions{

        width:100%;

    }

    .btn-portal,
    .btn-dashboard{

        width:100%;

    }

    .precio{

        font-size:38px;

    }

    .plan-card h3{

        font-size:30px;

    }

}

/* =========================================
🔥 ANIMACIÓN
========================================= */

@keyframes fadeIn{

    from{
        opacity:0;
        transform:translateY(20px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }

}

</style>

<div class="planes-modal">

    <div class="planes-content">

        <!-- HEADER -->
        <div class="planes-header">

            <h2>
                🚀 Elige tu plan perfecto
            </h2>

            <p>
                Desbloquea todo el potencial de tu negocio
            </p>

        </div>

        @if(isset($subscription) && $subscription && $subscription->status === 'activa')
            <div class="active-subscription-box">

                <div class="active-subscription-info">

                    <h3>
                        ✅ Ya tienes una suscripción activa
                    </h3>

                    <p>
                        @if(!empty($subscription->plan))
                            Plan actual:
                            <strong>
                                {{ ucfirst(str_replace('_', ' ', $subscription->plan)) }}
                            </strong>
                        @endif

                        @if(isset($diasRestantes) && $diasRestantes > 0)
                            — Restan
                            <strong>
                                {{ $diasRestantes }}
                            </strong>
                            día(s).
                        @endif
                    </p>

                </div>

                <div class="active-subscription-actions">

                    @if(!empty($subscription->stripe_customer_id))
                        <a
                            href="{{ route('portal.cliente') }}"
                            class="btn-portal">

                            Administrar suscripción

                        </a>
                    @endif

                    <a
                        href="{{ route('dashboard') }}"
                        class="btn-dashboard">

                        Ir al dashboard

                    </a>

                </div>

            </div>
        @endif

        <!-- TOGGLE -->
        <div class="billing-toggle">

            <div class="toggle-wrapper">

                <button
                    type="button"
                    class="toggle-btn active"
                    onclick="cambiarPlan('mensual')">

                    Mensual

                </button>

                <button
                    type="button"
                    class="toggle-btn"
                    onclick="cambiarPlan('anual')">

                    Anual

                </button>

            </div>

        </div>

        <!-- GRID -->
        <div class="plans-grid">

            <!-- BASICO -->
            <div class="plan-card">

                <div class="plan-icon icon-basic">
                    ✨
                </div>

                <h3>Básico</h3>

                <small>
                    Perfecto para empezar
                </small>

                <div class="price">

                    <span
                        class="precio"
                        data-mensual="549"
                        data-anual="458">

                        $549

                    </span>

                    <span> MXN/mes</span>

                </div>

                <p
                    class="annual-text"
                    data-mensual="Pago mensual de $549 MXN."
                    data-anual="Facturado anualmente: $5,490 MXN.">

                    Pago mensual de $549 MXN.

                </p>

                <p
                    class="save-text"
                    data-mensual=""
                    data-anual="Ahorras: $1,098 MXN.">

                </p>

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
                    type="button"
                    class="btn-plan"
                    onclick="seleccionarPlan('basico')">

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

                <small>
                    Para negocios en crecimiento
                </small>

                <div class="price">

                    <span
                        class="precio"
                        data-mensual="899"
                        data-anual="749">

                        $899

                    </span>

                    <span> MXN/mes</span>

                </div>

                <p
                    class="annual-text"
                    data-mensual="Pago mensual de $899 MXN."
                    data-anual="Facturado anualmente: $8,990 MXN.">

                    Pago mensual de $899 MXN.

                </p>

                <p
                    class="save-text"
                    data-mensual=""
                    data-anual="Ahorras: $1,798 MXN.">

                </p>

                <hr class="divider">

                <ul class="features">

                    <li>✔ Productos ilimitados</li>
                    <li>✔ Hasta 5 usuarios</li>
                    <li>✔ Ventas ilimitadas</li>
                    <li>✔ Reportes avanzados</li>
                    <li>✔ Soporte prioritario</li>
                    <li>✔ App móvil</li>
                    <li>✔ Múltiples sucursales</li>

                </ul>

                <button
                    type="button"
                    class="btn-plan"
                    onclick="seleccionarPlan('pro')">

                    Continuar con este plan

                </button>

            </div>

            <!-- NEGOCIO -->
            <div class="plan-card">

                <div class="plan-icon icon-business">
                    🚀
                </div>

                <h3>Negocio</h3>

                <small>
                    Para operaciones grandes
                </small>

                <div class="price">

                    <span
                        class="precio"
                        data-mensual="1499"
                        data-anual="1249">

                        $1499

                    </span>

                    <span> MXN/mes</span>

                </div>

                <p
                    class="annual-text"
                    data-mensual="Pago mensual de $1,499 MXN."
                    data-anual="Facturado anualmente: $14,990 MXN.">

                    Pago mensual de $1,499 MXN.

                </p>

                <p
                    class="save-text"
                    data-mensual=""
                    data-anual="Ahorras: $2,998 MXN.">

                </p>

                <hr class="divider">

                <ul class="features">

                    <li>✔ Todo lo del plan Pro</li>
                    <li>✔ Usuarios ilimitados</li>
                    <li>✔ Sucursales ilimitadas</li>
                    <li>✔ Reportes personalizados</li>
                    <li>✔ Soporte 24/7</li>

                </ul>

                <button
                    type="button"
                    class="btn-plan"
                    onclick="seleccionarPlan('negocio')">

                    Continuar con este plan

                </button>

            </div>

        </div>

        <!-- FOOTER -->
        <div class="plans-footer">

            <p class="footer-title">

                Todos los planes incluyen:

            </p>

            <div class="footer-items">

                <div>
                    🛡️ Pago seguro
                </div>

                <div>
                    ⚡ Activación inmediata
                </div>

                <div>
                    👥 Sin permanencia
                </div>

                <div>
                    📦 Cancela cuando quieras
                </div>

            </div>

        </div>

    </div>

</div>

<script>

let tipoFacturacion = 'mensual';

// =========================================
// 🔥 CAMBIAR PLAN
// =========================================

function cambiarPlan(tipo)
{
    tipoFacturacion = tipo;

    const botones =
        document.querySelectorAll('.toggle-btn');

    botones.forEach(btn => {

        btn.classList.remove('active');

    });

    if (tipo === 'mensual') {

        botones[0].classList.add('active');

    } else {

        botones[1].classList.add('active');

    }

    const precios =
        document.querySelectorAll('.precio');

    precios.forEach(precio => {

        if (tipo === 'mensual') {

            precio.innerText =
                '$' + Number(precio.dataset.mensual).toLocaleString('es-MX');

        } else {

            precio.innerText =
                '$' + Number(precio.dataset.anual).toLocaleString('es-MX');

        }

    });

    const annualTexts =
        document.querySelectorAll('.annual-text');

    annualTexts.forEach(text => {

        text.innerText = text.dataset[tipo] || '';

    });

    const saveTexts =
        document.querySelectorAll('.save-text');

    saveTexts.forEach(text => {

        text.innerText = text.dataset[tipo] || '';

    });

}

// =========================================
// 🔥 ENVIAR A CHECKOUT
// =========================================

function seleccionarPlan(plan)
{
    window.location.href =
        "{{ url('/checkout') }}" + '/' + plan + '?tipo=' + tipoFacturacion;
}

</script>

@endsection