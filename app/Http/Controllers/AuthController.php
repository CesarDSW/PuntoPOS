<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Company;
use App\Models\Subscription;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | REGISTRO DE USUARIO
    |--------------------------------------------------------------------------
    */

    // FORMULARIO REGISTRO
    public function showRegister()
    {
        $googleUser = session('google_user');

        return view('auth.register', compact('googleUser'));
    }

    // PROCESAR REGISTRO
    public function register(Request $request)
    {
        $request->validate([
            'name_user' => 'required|string|max:100',
            'phone' => 'required|string|max:10',
            'email' => 'required|email|unique:userr,email',
            'name_company' => 'required|string|max:100',
            'password' => 'required|min:8|confirmed',
        ]);

        $googleUser = session('google_user');

        // VALIDAR EMPRESA
        $companyExists = Company::where('name_company', $request->name_company)->exists();

        if ($companyExists) {
            return back()->withErrors([
                'name_company' => 'El nombre de la empresa ya existe. Por favor, elige otro nombre.'
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            // CREAR USUARIO
            $user = User::create([
                'name_user' => $request->name_user,
                'phone' => $request->phone,
                'email' => $request->email,
                'google_id' => $googleUser['google_id'] ?? null,
                'google_email' => $googleUser['google_email'] ?? null,
                'name_company' => $request->name_company,
                'password' => Hash::make($request->password),
                'rol_idfk' => 1,
                'company_idfk' => null,
                'state' => 1,
            ]);

            // CREAR EMPRESA
            $company = Company::create([
                'name_company' => $request->name_company,
                'owner_user_id' => $user->userr_id,
            ]);

            // RELACIONAR EMPRESA CON EL USUARIO
            $user->company_idfk = $company->company_id;
            $user->save();

            // CREAR SUSCRIPCIÓN / PRUEBA GRATUITA
            Subscription::create([
                'user_idfk' => $user->userr_id,
                'company_idfk' => $company->company_id,
                'status_subscription' => true,
                'status' => 'activa',
                'plan' => 'trial',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(14)->toDateString(),
            ]);
            
            DB::commit();

            // LIMPIAR SESIÓN GOOGLE
            session()->forget('google_user');

            return redirect()->route('login')->with('success', 'Usuario registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Ocurrió un error al registrar el usuario y la empresa: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    // FORMULARIO LOGIN
    public function showLogin()
    {
        return view('auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}