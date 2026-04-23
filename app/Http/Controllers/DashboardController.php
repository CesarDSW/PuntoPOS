<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Sale;
use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Product;

class DashboardController extends Controller

{

    // DASHBOARD
    public function showDashboard(Request $request)
    {
        $user = auth()->user();

        $company = Company::find($user->company_idfk);

        $showOnboarding = !$company || !$company->onboarding_completed;

        // 🔥 QUERY BASE
        $query = Sale::with(['customer', 'payment']);

        // 🔍 BUSCADOR (cliente + método)
        if ($request->search) {
            $query->where(function ($q) use ($request) {

                $q->whereHas('customer', function ($q2) use ($request) {
                    $q2->where('name_customer', 'like', '%' . $request->search . '%');
                })

                ->orWhereHas('payment', function ($q3) use ($request) {
                    $q3->where('payment_method', 'like', '%' . $request->search . '%');
                });

            });
        }

        // 📅 FILTRO FECHA
        if ($request->date) {
            $query->whereDate('date_time', $request->date);
        }

        // 📌 FILTRO ESTADO
        if ($request->status) {
            $query->whereHas('payment', function ($q) use ($request) {
                $q->where('status_payment', $request->status);
            });
        }

        // 🔽 TABLA
        $ventas = $query->orderBy('date_time', 'desc')->paginate(8);
        // ============================
        // 📊 CARDS (AQUÍ SE AGREGA)
        // ============================

        // 💰 Ventas del día
        $ventasDia = Sale::whereDate('date_time', Carbon::today())->sum('total');

        // 📦 Productos
        $productos = Product::count();

        // 👥 Clientes
        $clientes = Customer::count();

        // 🧾 Pedidos
        $pedidos = Sale::count();

        // ============================
        // 📊 VENTAS DE LA SEMANA
        // ============================
        $ventasSemana = [];

        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            $ventasSemana[] = Sale::whereDate('date_time', $fecha)->sum('total');
        }

        // ============================
        // 💳 MÉTODOS DE PAGO
        // ============================
        $metodosPago = [
            'efectivo' => 0,
            'tarjeta' => 0,
            'transferencia' => 0,
        ];

        $pagos = Sale::with('payment')->get();

        foreach ($pagos as $venta) {
            $metodo = strtolower($venta->payment->payment_method ?? '');

            if (isset($metodosPago[$metodo])) {
                $metodosPago[$metodo]++;
            }
        }
        

        // 🔥 RETURN FINAL (ACTUALIZADO)
        return view('dashboard', compact(
            'showOnboarding',
            'company',
            'ventas',
            'ventasSemana',
            'metodosPago',
            'ventasDia',
            'productos',
            'clientes',
            'pedidos'
        ));
    }

    // GUARDAR ONBOARDING
    public function storeOnboarding(Request $request)
    {
        $user = Auth::user();
        $company = Company::findOrFail($user->company_idfk);
        
        if($request->has('skip')){
            $company->update([
                'onboarding_completed' => 1,
            ]);

            return redirect()->route('dashboard');
        }

        $request->validate([
            'address' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:20',
            'opening_time' => 'nullable',
            'closing_time' => 'nullable',
            'payment_methods' => 'nullable|array',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $logoPath = $company->logo;

        if($request->hasFile('logo')){
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $company->update([
            'address' => $request->address,
            'currency' => $request->currency,
            'opening_time' => $request->opening_time,
            'closing_time' => $request->closing_time,
            'logo' => $logoPath,
            'payment_methods' => $request->payment_methods ? json_encode($request->payment_methods) : null,
            'onboarding_completed' => 1,
        ]);

        return redirect()->route('dashboard')->with('success', 'Configuración inicial guardada.');
    }
}