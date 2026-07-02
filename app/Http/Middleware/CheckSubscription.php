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

        // El rol DEV no se bloquea por suscripción
        if ($user->isDeveloper()) {
            return $next($request);
        }

        // Permitir acceso a pantallas de suscripción / checkout
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
            ->orderByDesc('subscription_id')
            ->first();

        // Si no hay suscripción, mandar a pagar
        if (!$subscription) {
            return redirect()->route('suscripcion');
        }

        $endDate = $subscription->end_date ? Carbon::parse($subscription->end_date) : null;

        // Si ya venció, marcarla vencida y bloquear
        if ($endDate && now()->gt($endDate->endOfDay())) {
            if ($subscription->status !== 'vencida' || (bool) $subscription->status_subscription !== false) {
                $subscription->update([
                    'status' => 'vencida',
                    'status_subscription' => false,
                ]);
            }

            return redirect()->route('suscripcion');
        }

        // Si sigue vigente, asegurar que quede activa
        if ($subscription->status !== 'activa' || (bool) $subscription->status_subscription !== true) {
            $subscription->update([
                'status' => 'activa',
                'status_subscription' => true,
            ]);
        }

        return $next($request);
    }
}