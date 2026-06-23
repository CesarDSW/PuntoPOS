<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isDeveloper()) {
            return $next($request);
        }

        if (
            $request->routeIs('suscripcion') ||
            $request->routeIs('checkout') ||
            $request->routeIs('crear.suscripcion') ||
            $request->routeIs('portal.cliente') ||
            $request->is('suscripcion*') ||
            $request->is('checkout*') ||
            $request->is('crear-suscripcion*') ||
            $request->is('portal-cliente*')
        ) {
            return $next($request);
        }

        $subscription = Subscription::where('company_idfk', $user->company_idfk)
            ->where('status', 'activa')
            ->orderBy('subscription_id', 'desc')
            ->first();

        if (!$subscription) {
            return redirect()->route('suscripcion');
        }

        if ($subscription->end_date && now()->greaterThan(Carbon::parse($subscription->end_date))) {
            $subscription->update([
                'status' => 'vencida',
                'status_subscription' => false,
            ]);

            return redirect()->route('suscripcion');
        }

        return $next($request);
    }
}