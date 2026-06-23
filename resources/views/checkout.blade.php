@extends('layout.dashboard_design')

@section('content')

<div class="checkout-container">
    <div class="checkout-card">
        <div class="checkout-header">
            <h1>Pago seguro</h1>

            <p>
                Estás suscribiéndote al plan
                <strong>{{ $planSeleccionado['nombre'] }}</strong>
            </p>

            <div class="price-box">
                ${{ number_format($precio) }} MXN

                <small>
                    / {{ $tipo }}
                </small>
            </div>
        </div>

        <form id="payment-form">
            @csrf

            <input type="hidden" id="selected-plan" value="{{ $plan }}_{{ $tipo }}">

            <div class="form-group">
                <label>Nombre del titular</label>

                <input
                    type="text"
                    id="card-holder-name"
                    placeholder="Nombre como aparece en la tarjeta"
                    required
                >
            </div>

            <div class="form-group">
                <label>Número de tarjeta</label>
                <div id="card-number" class="stripe-box"></div>
            </div>

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

            <button type="submit" id="card-button" class="pay-btn">
                Pagar suscripción
            </button>

            <a href="{{ route('suscripcion') }}" class="back-link">
                Volver a planes
            </a>

            <div id="card-errors"></div>
        </form>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    window.addEventListener('load', function () {
        const pageLoader = document.getElementById('page-loader');

        if (pageLoader) {
            pageLoader.style.display = 'none';
            pageLoader.classList.add('hidden');
            pageLoader.classList.remove('show', 'active');
        }

        document.body.classList.remove('loading', 'page-loading');
        document.documentElement.classList.remove('loading', 'page-loading');
    });

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            const pageLoader = document.getElementById('page-loader');

            if (pageLoader) {
                pageLoader.style.display = 'none';
                pageLoader.classList.add('hidden');
                pageLoader.classList.remove('show', 'active');
            }

            document.body.classList.remove('loading', 'page-loading');
            document.documentElement.classList.remove('loading', 'page-loading');
        }, 500);
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const stripeKey = @json(config('services.stripe.key'));
        const crearSuscripcionUrl = @json(route('crear.suscripcion'));
        const csrfToken = @json(csrf_token());
        const dashboardUrl = @json(route('dashboard'));
        const portalClienteUrl = @json(route('portal.cliente'));

        const form = document.getElementById('payment-form');
        const button = document.getElementById('card-button');
        const cardErrors = document.getElementById('card-errors');
        const cardHolderNameInput = document.getElementById('card-holder-name');
        const selectedPlanInput = document.getElementById('selected-plan');

        if (!stripeKey) {
            button.disabled = true;
            cardErrors.textContent = 'No está configurada la clave pública de Stripe.';

            Swal.fire({
                icon: 'error',
                title: 'Stripe no configurado',
                text: 'No está configurada la clave pública de Stripe.'
            });

            return;
        }

        if (typeof Stripe === 'undefined') {
            button.disabled = true;
            cardErrors.textContent = 'No se pudo cargar Stripe. Revisa si Opera GX, un bloqueador de anuncios o una extensión está bloqueando js.stripe.com.';

            Swal.fire({
                icon: 'error',
                title: 'Stripe fue bloqueado',
                text: 'No se pudo cargar Stripe. Desactiva el bloqueador de anuncios o la protección del navegador para este sitio.'
            });

            return;
        }

        const stripe = Stripe(stripeKey);
        const elements = stripe.elements();

        const style = {
            base: {
                fontSize: '16px',
                color: '#1e293b',
                fontFamily: 'Arial, sans-serif',
                '::placeholder': {
                    color: '#94a3b8'
                }
            },
            invalid: {
                color: '#dc2626'
            }
        };

        const cardNumber = elements.create('cardNumber', { style: style });
        const cardExpiry = elements.create('cardExpiry', { style: style });
        const cardCvc = elements.create('cardCvc', { style: style });

        cardNumber.mount('#card-number');
        cardExpiry.mount('#card-expiry');
        cardCvc.mount('#card-cvc');

        function showError(message) {
            const errorMessage = message || 'Ocurrió un error al procesar el pago.';

            cardErrors.textContent = errorMessage;

            Swal.fire({
                icon: 'error',
                title: 'Error al procesar el pago',
                text: errorMessage
            });
        }

        function disableButton() {
            button.disabled = true;
            button.textContent = 'Procesando...';
        }

        function enableButton() {
            button.disabled = false;
            button.textContent = 'Pagar suscripción';
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            cardErrors.textContent = '';

            const cardHolderName = cardHolderNameInput.value.trim();
            const selectedPlan = selectedPlanInput.value;

            if (!cardHolderName) {
                showError('Escribe el nombre del titular de la tarjeta.');
                return;
            }

            if (!selectedPlan) {
                showError('No se encontró el plan seleccionado.');
                return;
            }

            disableButton();

            Swal.fire({
                title: 'Procesando pago...',
                text: 'Por favor espera un momento.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });

            try {
                const paymentMethodResult = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardNumber,
                    billing_details: {
                        name: cardHolderName
                    }
                });

                if (paymentMethodResult.error) {
                    Swal.close();
                    enableButton();
                    showError(paymentMethodResult.error.message);
                    return;
                }

                const paymentMethod = paymentMethodResult.paymentMethod;

                const response = await fetch(crearSuscripcionUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        payment_method: paymentMethod.id,
                        plan: selectedPlan
                    })
                });

                const data = await response.json().catch(function () {
                    return null;
                });

                if (!response.ok) {
                    Swal.close();
                    enableButton();

                    showError(
                        data && data.error
                            ? data.error
                            : 'No se pudo crear la suscripción.'
                    );

                    return;
                }

                if (!data || data.success !== true) {
                    Swal.close();
                    enableButton();

                    showError(
                        data && data.error
                            ? data.error
                            : 'El servidor no devolvió una respuesta válida.'
                    );

                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Suscripción activada',
                    text: data.message || 'Tu suscripción se activó correctamente.',
                    confirmButtonText: 'Ir al dashboard',
                    showDenyButton: true,
                    denyButtonText: 'Ir al portal del cliente',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.location.href = data.redirect_url || dashboardUrl;
                        return;
                    }

                    if (result.isDenied) {
                        window.location.href = portalClienteUrl;
                        return;
                    }
                });
                
            } catch (err) {
                Swal.close();
                enableButton();

                showError(
                    err && err.message
                        ? err.message
                        : 'No se pudo conectar con el servidor.'
                );
            }
        });
    });
</script>

<style>
.checkout-container {
    min-height: calc(100vh - 80px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background:
        radial-gradient(circle at top left, rgba(37, 99, 235, 0.12), transparent 35%),
        #f8fafc;
}

.checkout-card {
    width: 100%;
    max-width: 520px;
    background: #ffffff;
    border-radius: 24px;
    padding: 36px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
    border: 1px solid #e2e8f0;
}

.checkout-header {
    text-align: center;
    margin-bottom: 28px;
}

.checkout-header h1 {
    margin: 0;
    color: #0f172a;
    font-size: 32px;
    font-weight: 800;
}

.checkout-header p {
    margin: 12px 0 0;
    color: #64748b;
    font-size: 16px;
}

.price-box {
    margin: 24px auto 0;
    padding: 20px;
    border-radius: 18px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff;
    font-size: 34px;
    font-weight: 800;
}

.price-box small {
    display: block;
    margin-top: 8px;
    font-size: 15px;
    color: rgba(255, 255, 255, 0.85);
    font-weight: 500;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    color: #0f172a;
    font-weight: 700;
    margin-bottom: 8px;
}

.form-group input {
    width: 100%;
    height: 48px;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    padding: 0 14px;
    font-size: 15px;
    outline: none;
}

.form-group input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.card-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.stripe-box {
    min-height: 48px;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    padding: 14px;
    background: #ffffff;
}

.stripe-box:focus-within {
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.pay-btn {
    width: 100%;
    height: 52px;
    border: none;
    border-radius: 16px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
    margin-top: 8px;
}

.pay-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 28px rgba(37, 99, 235, 0.25);
}

.pay-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.back-link {
    display: block;
    text-align: center;
    margin-top: 18px;
    color: #2563eb;
    text-decoration: none;
    font-weight: 700;
}

#card-errors {
    margin-top: 16px;
    color: #dc2626;
    font-weight: 600;
    text-align: center;
}

#page-loader.page-loader {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
}

@media (max-width: 640px) {
    .checkout-card {
        padding: 24px;
    }

    .card-row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .checkout-header h1 {
        font-size: 26px;
    }

    .price-box {
        font-size: 28px;
    }
}
</style>

@endsection