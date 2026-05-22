@extends('layout.dashboard_design')

@section('content')

<div class="checkout-container">

    <div class="checkout-card">

        <div class="checkout-header">

            <h1>Pago seguro 💳</h1>

            <p>
                Estás suscribiéndote al plan
                <strong>{{ $planSeleccionado['nombre'] }}</strong>
            </p>

            <!-- PRECIO -->
            <div class="price-box">

                ${{ number_format($precio) }} MXN

                <small
                    style="
                        display:block;
                        margin-top:8px;
                        font-size:15px;
                        color:#64748b;
                    ">

                    / {{ $tipo }}

                </small>

            </div>

        </div>

        <form id="payment-form">

            @csrf

            <!-- NOMBRE -->
            <div class="form-group">

                <label>Nombre del titular</label>

                <input
                    type="text"
                    id="card-holder-name"
                    placeholder="Nombre como aparece en la tarjeta"
                    required
                >

            </div>

            <!-- TARJETA -->
            <div class="form-group">

                <label>Número de tarjeta</label>

                <div id="card-number" class="stripe-box"></div>

            </div>

            <!-- FECHA Y CVV -->
            <div class="card-row">

                <div class="form-group">

                    <label>Fecha de expiración</label>

                    <div id="card-expiry" class="stripe-box"></div>

                </div>

                <div class="form-group">

                    <label>CVV</label>

                    <div id="card-cvc" class="stripe-box"></div>

                </div>

            </div>

            <!-- BOTÓN -->
            <button
                type="submit"
                id="card-button"
                class="pay-btn">

                Pagar suscripción

            </button>

            <!-- ERRORES -->
            <div id="card-errors"></div>

        </form>

    </div>

</div>

<!-- STRIPE -->
<script src="https://js.stripe.com/v3/"></script>

<!-- SWEET ALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    // =========================================
    // 🔥 STRIPE
    // =========================================

    const stripe = Stripe(
        "{{ config('services.stripe.key') }}"
    );

    const elements = stripe.elements();

    // =========================================
    // 🔥 ESTILOS
    // =========================================

    const style = {

        base: {

            fontSize: '16px',

            color: '#32325d',

            fontFamily: 'Arial, sans-serif',

            '::placeholder': {

                color: '#a0aec0'

            }

        }

    };

    // =========================================
    // 🔥 ELEMENTOS
    // =========================================

    const cardNumber =
        elements.create('cardNumber', { style });

    const cardExpiry =
        elements.create('cardExpiry', { style });

    const cardCvc =
        elements.create('cardCvc', { style });

    // =========================================
    // 🔥 MOUNT
    // =========================================

    cardNumber.mount('#card-number');

    cardExpiry.mount('#card-expiry');

    cardCvc.mount('#card-cvc');

    // =========================================
    // 🔥 FORM
    // =========================================

    const form =
        document.getElementById('payment-form');

    form.addEventListener('submit', async (e) => {

        e.preventDefault();

        // =========================================
        // 🔥 LOADING
        // =========================================

        Swal.fire({

            title: 'Procesando pago...',

            text: 'Por favor espera',

            allowOutsideClick: false,

            didOpen: () => {

                Swal.showLoading();

            }

        });

        // =========================================
        // 🔥 PAYMENT METHOD
        // =========================================

        const {
            paymentMethod,
            error

        } = await stripe.createPaymentMethod({

            type: 'card',

            card: cardNumber,

            billing_details: {

                name:
                    document.getElementById(
                        'card-holder-name'
                    ).value

            }

        });

        // =========================================
        // 🔥 ERROR STRIPE
        // =========================================

        if (error) {

            Swal.close();

            Swal.fire({

                title: 'Tarjeta inválida',

                text: error.message,

                icon: 'warning',

                confirmButtonColor: '#f59e0b'

            });

            return;

        }

        // =========================================
        // 🔥 ENVIAR A LARAVEL
        // =========================================

        fetch('/crear-suscripcion', {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

                'X-CSRF-TOKEN':
                    '{{ csrf_token() }}'

            },

            body: JSON.stringify({

                payment_method:
                    paymentMethod.id,

                // 🔥 PLAN
                plan:
                    "{{ $plan }}_{{ $tipo }}"

            })

        })

        .then(res => res.json())

        .then(data => {

            Swal.close();

            // =========================================
            // 🔥 ÉXITO
            // =========================================

            if (data.success) {

                Swal.fire({

                    title: '¡Pago exitoso!',

                    text:
                        'Tu suscripción fue activada correctamente',

                    icon: 'success',

                    showDenyButton: true,

                    confirmButtonText:
                        'Ir al dashboard',

                    denyButtonText:
                        'Portal del cliente',

                    confirmButtonColor:
                        '#2563eb',

                    denyButtonColor:
                        '#111827'

                }).then((result) => {

                    // DASHBOARD
                    if (result.isConfirmed) {

                        window.location.href =
                            "{{ route('dashboard') }}";

                    }

                    // PORTAL CLIENTE
                    else if (result.isDenied) {

                        window.location.href =
                            '/portal-cliente';

                    }

                });

            } else {

                // =========================================
                // 🔥 ERROR
                // =========================================

                Swal.fire({

                    title: 'Error',

                    text:
                        data.error
                        ||
                        'Error al procesar el pago',

                    icon: 'error',

                    confirmButtonColor:
                        '#dc2626'

                });

            }

        })

        .catch(error => {

            Swal.close();

            Swal.fire({

                title: 'Error inesperado',

                text:
                    'Ocurrió un problema al procesar el pago',

                icon: 'error',

                confirmButtonColor:
                    '#dc2626'

            });

            console.error(error);

        });

    });

</script>

<style>

.checkout-container{

    display:flex;

    justify-content:center;

    align-items:center;

    min-height:100vh;

    padding:40px;

    background:
    linear-gradient(
        135deg,
        #eff6ff,
        #eef2ff
    );

}

.checkout-card{

    width:100%;

    max-width:520px;

    background:#fff;

    border-radius:30px;

    padding:40px;

    box-shadow:
    0 20px 45px rgba(0,0,0,0.08);

    animation:fadeIn .4s ease;

}

.checkout-header{

    text-align:center;

    margin-bottom:30px;

}

.checkout-header h1{

    font-size:36px;

    margin-bottom:10px;

    color:#111827;

}

.checkout-header p{

    color:#6b7280;

    font-size:15px;

}

.price-box{

    margin-top:25px;

    background:
    linear-gradient(
        135deg,
        #eff6ff,
        #dbeafe
    );

    color:#2563eb;

    padding:22px;

    border-radius:20px;

    font-size:40px;

    font-weight:700;

}

.form-group{

    margin-bottom:24px;

}

.form-group label{

    display:block;

    margin-bottom:8px;

    font-weight:600;

    color:#111827;

}

.form-group input{

    width:100%;

    padding:15px;

    border:1px solid #d1d5db;

    border-radius:16px;

    font-size:15px;

    outline:none;

    transition:.3s;

    box-sizing:border-box;

}

.form-group input:focus{

    border-color:#2563eb;

    box-shadow:
    0 0 0 4px rgba(37,99,235,0.15);

}

.stripe-box{

    padding:16px;

    border:1px solid #d1d5db;

    border-radius:16px;

    background:#fff;

    transition:.3s;

}

.stripe-box:focus-within{

    border-color:#2563eb;

    box-shadow:
    0 0 0 4px rgba(37,99,235,0.15);

}

.card-row{

    display:flex;

    gap:15px;

}

.card-row .form-group{

    flex:1;

}

.pay-btn{

    width:100%;

    border:none;

    padding:17px;

    border-radius:18px;

    background:
    linear-gradient(
        135deg,
        #2563eb,
        #1d4ed8
    );

    color:#fff;

    font-size:17px;

    font-weight:700;

    cursor:pointer;

    transition:.3s;

    margin-top:10px;

}

.pay-btn:hover{

    transform:translateY(-2px);

    box-shadow:
    0 12px 25px rgba(37,99,235,0.25);

}

#card-errors{

    margin-top:15px;

    color:red;

    text-align:center;

}

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

@endsection