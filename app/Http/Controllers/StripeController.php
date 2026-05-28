<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    public function crearSuscripcion(Request $request)
    {
        try {

            Stripe::setApiKey(
                config('services.stripe.secret')
            );

            $paymentMethod = $request->payment_method;

            if (!$paymentMethod) {

                return response()->json([

                    'error' =>
                        'No se recibió método de pago'

                ], 400);

            }

            $user = auth()->user();

            if (!$user) {

                return response()->json([

                    'error' =>
                        'Usuario no autenticado'

                ], 401);

            }

            $email = $user->email
                ?? $user->email_user
                ?? null;

            if (!$email) {

                return response()->json([

                    'error' =>
                        'Usuario sin email'

                ], 400);

            }

            $customer = \Stripe\Customer::create([

                'email' => $email,

            ]);

            $pm = \Stripe\PaymentMethod::retrieve(
                $paymentMethod
            );

            $pm->attach([

                'customer' => $customer->id,

            ]);

            \Stripe\Customer::update(

                $customer->id,

                [

                    'invoice_settings' => [

                        'default_payment_method' =>
                            $paymentMethod,

                    ],

                ]

            );

            $plan = $request->plan;

            $precios = [

                'basico_mensual' =>
                    'price_1TZZSVBABidtfriTVCFVLawm',

                'basico_anual' =>
                    'price_1TZZXxBABidtfriT7IaIxKwjL',

                'pro_mensual' =>
                    'price_1TZZZ6BABidtfriTdZ7IJqBL',

                'pro_anual' =>
                    'price_1TZZZsBABidtfriTiX6qBGdi',

                'negocio_mensual' =>
                    'price_1TZZagBABidtfriT24ZRh9eI',

                'negocio_anual' =>
                    'price_1TZZbHBABidtfriTQVk37YUM',

            ];

            if (!isset($precios[$plan])) {

                return response()->json([

                    'error' => 'Plan inválido'

                ], 400);

            }

            $priceId = $precios[$plan];

            $subscriptionStripe =
                \Stripe\Subscription::create([

                    'customer' => $customer->id,

                    'items' => [

                        [

                            'price' => $priceId

                        ]

                    ],

                ]);

            $subscriptionLocal =
                Subscription::where(

                    'user_idfk',
                    $user->userr_id

                )
                ->orderBy(
                    'subscription_id',
                    'desc'
                )
                ->first();

            if ($subscriptionLocal) {

                Subscription::where(
                    'user_idfk',
                    $user->userr_id
                )
                ->where(
                    'subscription_id',
                    '!=',
                    $subscriptionLocal->subscription_id
                )
                ->update([

                    'status' => 'cancelada'

                ]);

                $subscriptionLocal->update([

                    'stripe_customer_id' =>
                        $customer->id,

                    'stripe_subscription_id' =>
                        $subscriptionStripe->id,

                    'status' => 'activa',

                    'plan' => $plan,

                    'start_date' => now(),

                    'end_date' => now()->addMonth(),

                ]);

            } else {

                Subscription::create([

                    'user_idfk' =>
                        $user->userr_id,

                    'company_idfk' =>
                        $user->company_idfk ?? 1,

                    'stripe_customer_id' =>
                        $customer->id,

                    'stripe_subscription_id' =>
                        $subscriptionStripe->id,

                    'status' => 'activa',

                    'plan' => $plan,

                    'start_date' => now(),

                    'end_date' => now()->addMonth(),

                ]);

            }

            return response()->json([

                'success' => true

            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Stripe error: ' . $e->getMessage()
            );

            return response()->json([

                'error' => $e->getMessage()

            ], 500);

        }
    }

    public function verSuscripcion()
    {
        $user = auth()->user();

        if (!$user) {

            return redirect('/login');

        }

        $subscription = Subscription::where(

            'user_idfk',
            $user->userr_id

        )
        ->where('status', 'activa')
        ->first();

        $diasRestantes = 0;

        if ($subscription) {

            $diasRestantes = ceil(

                now()->diffInHours(
                    $subscription->end_date,
                    false
                ) / 24

            );

        }

        return view(

            'suscripcion',

            compact(

                'subscription',

                'diasRestantes'

            )

        );
    }

    public function portalCliente()
    {
        try {

            Stripe::setApiKey(
                config('services.stripe.secret')
            );

            $user = auth()->user();

            if (!$user) {

                return redirect()->back()->with(

                    'error',
                    'Usuario no autenticado'

                );

            }

            $subscription = Subscription::where(

                'user_idfk',
                $user->userr_id

            )
            ->where('status', 'activa')
            ->first();

            if (!$subscription) {

                return redirect()->back()->with(

                    'error',
                    'No tienes una suscripción activa'

                );

            }

            $session =
                \Stripe\BillingPortal\Session::create([

                    'customer' =>
                        $subscription->stripe_customer_id,

                    'return_url' =>
                        route('dashboard'),

                ]);

            return redirect($session->url);

        } catch (\Throwable $e) {

            Log::error(
                'Stripe portal error: ' . $e->getMessage()
            );

            return redirect()->back()->with(

                'error',
                $e->getMessage()

            );

        }
    }

    public function checkout($plan)
    {
        $tipo = request('tipo', 'mensual');

        $planes = [

            'basico' => [

                'nombre' => 'Básico',

                'mensual' => 549,

                'anual' => 458

            ],

            'pro' => [

                'nombre' => 'Pro',

                'mensual' => 899,

                'anual' => 749

            ],

            'negocio' => [

                'nombre' => 'Negocio',

                'mensual' => 1499,

                'anual' => 1249

            ]

        ];

        if (!isset($planes[$plan])) {

            abort(404);

        }

        $planSeleccionado = $planes[$plan];

        $precio = $planSeleccionado[$tipo];

        return view(
            'checkout',
            compact(
                'planSeleccionado',
                'precio',
                'tipo',
                'plan'
            )
        );
    }
}