<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeController extends Controller
{
    public function verSuscripcion()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isDeveloper()) {
            return redirect()->route('developer.support.index');
        }


        $subscription = Subscription::where('company_idfk', $user->company_idfk)
            ->where('status', 'activa')
            ->orderBy('subscription_id', 'desc')
            ->first();

        $diasRestantes = 0;

        if ($subscription && $subscription->end_date) {
            $diasRestantes = ceil(
                now()->diffInHours($subscription->end_date, false) / 24
            );
        }

        return view('suscripcion', compact('subscription', 'diasRestantes'));
    }

    public function checkout($plan)
    {
        $user = auth()->user();

        if ($user && $user->isDeveloper()) {
            return redirect()->route('developer.support.index');
        }

        $tipo = request('tipo', 'mensual');

        $planes = [
            'basico' => [
                'nombre' => 'Básico',
                'mensual' => 549,
                'anual' => 458,
            ],

            'pro' => [
                'nombre' => 'Pro',
                'mensual' => 899,
                'anual' => 749,
            ],

            'negocio' => [
                'nombre' => 'Negocio',
                'mensual' => 1499,
                'anual' => 1249,
            ],
        ];

        if (!isset($planes[$plan])) {
            abort(404);
        }

        if (!in_array($tipo, ['mensual', 'anual'], true)) {
            abort(404);
        }

        $planSeleccionado = $planes[$plan];
        $precio = $planSeleccionado[$tipo];

        return view('checkout', compact(
            'planSeleccionado',
            'precio',
            'tipo',
            'plan'
        ));
    }

    public function crearSuscripcion(Request $request)
    {
        $request->validate([
            'payment_method' => ['required', 'string'],
            'plan' => ['required', 'string'],
        ]);

        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuario no autenticado.',
                ], 401);
            }

            if ($user->isDeveloper()) {
                return response()->json([
                    'success' => false,
                    'error' => 'La cuenta DEV no require suscripción.',
                    'redirect_url' => route('developer.support.index'),
                ], 403);
            }

            if (!$user->company_idfk) {
                return response()->json([
                    'success' => false,
                    'error' => 'Tu cuenta no tiene empresa asignada.',
                ], 422);
            }

            $stripeSecret = config('services.stripe.secret');

            if (!$stripeSecret) {
                return response()->json([
                    'success' => false,
                    'error' => 'No está configurada la clave secreta de Stripe.',
                ], 500);
            }

            $email = $user->email
                ?? $user->email_user
                ?? $user->google_email
                ?? null;

            if (!$email) {
                return response()->json([
                    'success' => false,
                    'error' => 'El usuario no tiene correo registrado.',
                ], 422);
            }

            $priceId = $this->getStripePriceId($request->plan);

            if (!$priceId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Plan inválido o Price ID no configurado en .env.',
                ], 422);
            }

            $stripe = new StripeClient($stripeSecret);

            $currentSubscription = Subscription::where('company_idfk', $user->company_idfk)
                ->orderBy('subscription_id', 'desc')
                ->first();

            $customerId = $currentSubscription?->stripe_customer_id;

            if (!$customerId) {
                $customer = $stripe->customers->create([
                    'email' => $email,
                    'name' => $user->name_user ?? null,
                    'metadata' => [
                        'userr_id' => $user->userr_id,
                        'company_idfk' => $user->company_idfk,
                    ],
                ]);

                $customerId = $customer->id;
            }

            $stripe->paymentMethods->attach(
                $request->payment_method,
                [
                    'customer' => $customerId,
                ]
            );

            $stripe->customers->update(
                $customerId,
                [
                    'invoice_settings' => [
                        'default_payment_method' => $request->payment_method,
                    ],
                ]
            );

            $stripeSubscription = $stripe->subscriptions->create([
                'customer' => $customerId,
                'items' => [
                    [
                        'price' => $priceId,
                    ],
                ],
                'default_payment_method' => $request->payment_method,
                'metadata' => [
                    'userr_id' => $user->userr_id,
                    'company_idfk' => $user->company_idfk,
                    'plan' => $request->plan,
                ],
                'expand' => [
                    'latest_invoice.payment_intent',
                ],
            ]);

            Subscription::where('company_idfk', $user->company_idfk)
                ->where('status', 'activa')
                ->update([
                    'status' => 'cancelada',
                    'status_subscription' => false,
                ]);

            Subscription::create([
                'user_idfk' => $user->userr_id,
                'company_idfk' => $user->company_idfk,
                'stripe_customer_id' => $customerId,
                'stripe_subscription_id' => $stripeSubscription->id,
                'status_subscription' => true,
                'status' => 'activa',
                'plan' => $request->plan,
                'start_date' => now()->toDateString(),
                'end_date' => $this->calculateEndDate($request->plan),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Suscripción creada correctamente.',
                'redirect_url' => route('dashboard'),
            ]);

        } catch (\Throwable $e) {
            Log::error('Stripe subscription error', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'plan' => $request->plan ?? null,
            ]);

            return response()->json([
                'success' => false,
                'error' => $this->formatStripeError($e->getMessage()),
            ], 500);
        }
    }

    public function portalCliente()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return redirect()->route('login');
            }

            $subscription = Subscription::where('company_idfk', $user->company_idfk)
                ->where('status', 'activa')
                ->orderBy('subscription_id', 'desc')
                ->first();

            if (!$subscription || !$subscription->stripe_customer_id) {
                return redirect()
                    ->route('suscripcion')
                    ->with('error', 'No tienes una suscripción activa para abrir el portal de cliente.');
            }

            $stripeSecret = config('services.stripe.secret');

            if (!$stripeSecret) {
                return redirect()
                    ->route('suscripcion')
                    ->with('error', 'No está configurada la clave secreta de Stripe.');
            }

            $stripe = new StripeClient($stripeSecret);

            $session = $stripe->billingPortal->sessions->create([
                'customer' => $subscription->stripe_customer_id,
                'return_url' => route('dashboard'),
            ]);

            return redirect($session->url);

        } catch (\Throwable $e) {
            Log::error('Stripe portal error', [
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('suscripcion')
                ->with('error', $this->formatStripeError($e->getMessage()));
        }
    }

    private function getStripePriceId(string $plan): ?string
    {
        $prices = config('services.stripe.prices');

        return $prices[$plan] ?? null;
    }

    private function calculateEndDate(string $plan): string
    {
        if (str_ends_with($plan, '_anual')) {
            return now()->addYear()->toDateString();
        }

        return now()->addMonth()->toDateString();
    }

    private function formatStripeError(string $message): string
    {
        $lowerMessage = strtolower($message);

        if (str_contains($lowerMessage, 'no such price')) {
            return 'El Price ID de Stripe no existe o no pertenece al entorno configurado.';
        }

        if (str_contains($lowerMessage, 'no such customer')) {
            return 'El cliente de Stripe no existe. Intenta crear la suscripción nuevamente.';
        }

        if (str_contains($lowerMessage, 'no such paymentmethod')) {
            return 'El método de pago no existe o no fue creado correctamente.';
        }

        if (str_contains($lowerMessage, 'api key')) {
            return 'Las claves de Stripe no están configuradas correctamente.';
        }

        if (str_contains($lowerMessage, 'your card was declined')) {
            return 'La tarjeta fue rechazada por Stripe.';
        }

        if (str_contains($lowerMessage, 'authentication_required')) {
            return 'La tarjeta requiere autenticación adicional.';
        }

        return $message ?: 'No se pudo procesar la suscripción.';
    }
}