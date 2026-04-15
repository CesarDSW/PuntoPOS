<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Sale;

class DashboardController extends Controller
{

    // DASHBOARD
    public function showDashboard(){
        $user = auth()->user();

        $company = Company::find($user->company_idfk);

        $showOnboarding = !$company || !$company->onboarding_completed;

        $ventas = Sale::orderBy('date_time', 'desc')->take(5)->get();

        return view('dashboard', compact(
            'showOnboarding',
            'company',
            'ventas'
        ));
    }

    // GUARDAR ONBOARDING
    public function storeOnboarding(Request $request)
    {
        $user = Auth::user();
        $company = Company::findOrFail($user->company_idfk);
        
        // OMITIR
        if($request->has('skip')){
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

        if($request->hasFile('logo')){
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

        return redirect()->route('dashboard')->with('success', 'Configuración inicial guardada.');
    }

}