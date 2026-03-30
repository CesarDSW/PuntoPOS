<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Company;


class AuthController extends Controller
{
    /*Registro de usuario*/
    //Funcion que manda a llamar el formulario de registro
    public function showRegister()
    {
        $googleUser = session('google_user');

        return view('auth.register', compact('googleUser'));
    }

    //Funcion que procesa el formulario de registro
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

        $companyExists = Company::where('name_company', $request->name_company)->exists();

        if ($companyExists) {
            return back()->withErrors([
                'name_company' => 'El nombre de la empresa ya existe. Por favor, elige otro nombre.'
            ]);
        }

        DB::beginTransaction();

        try {
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

            $company = Company::Create([
                'name_company' => $request->name_company,
                'owner_user_id' => $user->userr_id,
            ]);

            $user->company_idfk = $company->company_id;
            $user->save();        
            
            DB::commit();

            session() ->forget('google_user');
            
            return redirect()->route('login')->with('success', 'Usuario registrado exitosamente.');
            
            } catch(\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Ocurrio un error al registrar el usuario y la empresa: '. $e->getMessage()
            ])->withInput();
        }   
    }

    /*Inicio de sesion*/
    //Funcion que manda a llamar el formulario de inicio de sesion.
    public function showLogin()
    {
        return view('auth.login');
    }

    //Funcion que procesa el formulario de inicio de sesion
    /*public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden.',
        ]) ->onlyInput('email');
    }*/

    //Funcion que cierra la sesion del usuario
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}