<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Sale;
use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Subscription;

class DashboardController extends Controller
{
    // DASHBOARD
    public function showDashboard(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isDeveloper()) {
            return redirect()->route('developer.support.index');
        }

        if (!$user->company_idfk) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Tu cuenta no tiene empresa asignada. Contacta al administrador.',
                ]);
        }

        // =========================================
        // BUSCAR ÚLTIMA SUSCRIPCIÓN POR EMPRESA
        // =========================================
        $subscription = Subscription::where(
            'company_idfk',
            $user->company_idfk
        )
        ->latest('subscription_id')
        ->first();

        // =========================================
        // SI NO EXISTE, CREAR TRIAL
        // =========================================
        if (!$subscription) {
            $subscription = Subscription::create([
                'company_idfk' => $user->company_idfk,
                'status_subscription' => true,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(14)->toDateString(),
            ]);
        }

        // =========================================
        // SI ESTÁ DESACTIVADA
        // =========================================
        if (!$subscription->status_subscription) {
            return redirect()->route('suscripcion');
        }

        // =========================================
        // SI YA VENCIÓ
        // =========================================
        if (
            $subscription->end_date &&
            now()->greaterThan(Carbon::parse($subscription->end_date))
        ) {
            $subscription->update([
                'status_subscription' => false
            ]);

            return redirect()->route('suscripcion');
        }

        // =========================================
        // TIEMPO RESTANTE
        // =========================================
        $minutosRestantes = now()->diffInMinutes(
            Carbon::parse($subscription->end_date),
            false
        );

        $minutosRestantes = max(0, $minutosRestantes);

        if ($minutosRestantes >= 1440) {
            $diasRestantes = floor($minutosRestantes / 1440) . ' días';
        } elseif ($minutosRestantes >= 60) {
            $diasRestantes = floor($minutosRestantes / 60) . ' horas';
        } else {
            $diasRestantes = $minutosRestantes . ' min';
        }

        $company = Company::find($user->company_idfk);

        $showOnboarding = !$company || !$company->onboarding_completed;

        // =========================================
        // QUERY BASE
        // =========================================
        $query = Sale::with([
            'customer',
            'payment'
        ]);

        // =========================================
        // BUSCADOR
        // =========================================
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->whereHas(
                    'customer',
                    function ($q2) use ($request) {
                        $q2->where(
                            'name_customer',
                            'like',
                            '%' . $request->search . '%'
                        );
                    }
                )
                ->orWhereHas(
                    'payment',
                    function ($q3) use ($request) {
                        $q3->where(
                            'payment_method',
                            'like',
                            '%' . $request->search . '%'
                        );
                    }
                );
            });
        }

        // =========================================
        // FILTRO FECHA
        // =========================================
        if ($request->date) {
            $query->whereDate(
                'date_time',
                $request->date
            );
        }

        // =========================================
        // FILTRO ESTADO
        // =========================================
        if ($request->status) {
            $query->whereHas(
                'payment',
                function ($q) use ($request) {
                    $q->where(
                        'status_payment',
                        $request->status
                    );
                }
            );
        }

        $ventas = $query
            ->orderBy('date_time', 'desc')
            ->paginate(8);

        // =========================================
        // CARDS
        // =========================================
        $ventasDia = Sale::whereDate(
            'date_time',
            Carbon::today()
        )->sum('total');

        $productos = Product::count();
        $clientes = Customer::count();
        $pedidos = Sale::count();

        // =========================================
        // PAGOS EXITOSOS
        // =========================================
        $pagosExitosos = Sale::whereHas(
            'payment',
            function ($q) {
                $q->where(
                    'status_payment',
                    'success'
                );
            }
        )->count();

        // =========================================
        // PAGOS FALLIDOS
        // =========================================
        $pagosFallidos = Sale::whereHas(
            'payment',
            function ($q) {
                $q->where(
                    'status_payment',
                    'failed'
                );
            }
        )->count();

        // =========================================
        // PAGOS PENDIENTES
        // =========================================
        $pagosPendientes = Sale::whereHas(
            'payment',
            function ($q) {
                $q->where(
                    'status_payment',
                    'pending'
                );
            }
        )->count();

        // =========================================
        // VENTAS SEMANA
        // =========================================
        $ventasSemana = [];

        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);

            $ventasSemana[] = Sale::whereDate(
                'date_time',
                $fecha
            )->sum('total');
        }

        // =========================================
        // MÉTODOS DE PAGO
        // =========================================
        $metodosPago = [
            'efectivo' => 0,
            'tarjeta' => 0,
            'transferencia' => 0,
        ];

        $pagos = Sale::with('payment')->get();

        foreach ($pagos as $venta) {
            $metodo = strtolower(
                $venta->payment->payment_method ?? ''
            );

            if (isset($metodosPago[$metodo])) {
                $metodosPago[$metodo]++;
            }
        }

        // =========================================
        // RETURN
        // =========================================
        return view('dashboard', compact(
            'showOnboarding',
            'company',
            'ventas',
            'ventasSemana',
            'metodosPago',
            'ventasDia',
            'productos',
            'clientes',
            'pedidos',
            'pagosExitosos',
            'pagosFallidos',
            'pagosPendientes',
            'diasRestantes',
            'subscription'
        ));
    }

    // GUARDAR ONBOARDING
    public function storeOnboarding(Request $request)
    {
        $user = Auth::user();
        $company = Company::findOrFail($user->company_idfk);

        // OMITIR
        if ($request->has('skip')) {
            $company->update([
                'onboarding_completed' => 1,
            ]);

            return redirect()->route('dashboard');
        }

        // VALIDACIÓN
        $request->validate([
            'address' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:20',
            'opening_time' => 'nullable',
            'closing_time' => 'nullable',
            'payment_methods' => 'nullable|array',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $logoPath = $company->logo;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        // GUARDAR
        $company->update([
            'address' => $request->address,
            'currency' => $request->currency,
            'opening_time' => $request->opening_time,
            'closing_time' => $request->closing_time,
            'logo' => $logoPath,
            'payment_methods' => $request->payment_methods ? json_encode($request->payment_methods) : null,
            'onboarding_completed' => 1,
        ]);

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'Configuración inicial guardada.'
            );
    }
}