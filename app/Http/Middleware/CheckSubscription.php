<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Subscription;
use Carbon\Carbon;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // 🔥 Si no hay login
        if (!$user) {

            return redirect()->route('login');

        }

        // 🔥 Buscar suscripción
        $subscription = Subscription::where(
            'user_idfk',
            $user->userr_id
        )
        ->latest('subscription_id')
        ->first();

        // 🔥 Si no tiene suscripción
        if (!$subscription) {

            return redirect()->route('suscripcion');

        }

        // 🔥 Si la trial venció
        if (

            $subscription->plan === 'trial'

            &&

            now()->greaterThan(
                Carbon::parse(
                    $subscription->end_date
                )
            )

        ) {

            // 🔥 Cambiar status
            $subscription->update([

                'status' => 'vencida'

            ]);

            // 🔥 Bloquear sistema
            return redirect()->route('suscripcion');

        }

        return $next($request);
    }
}