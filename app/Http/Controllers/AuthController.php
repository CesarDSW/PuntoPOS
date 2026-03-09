<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //Registro de usuario
        //Funcion que manda a llamar el formulario de registro
    public function showRegister()
    {
        return view('auth.register');
    }

        //Funcion que procesa el formulario de registro
    public function register(Request $request)
    {
        $request->validate([
            'name_user' => 'required',
            'phone' => 'required',
            'email' => 'required|email|unique:userr,email',
            'name_company' => 'required',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name_user' => $request->name_user,
            'phone' => $request->phone,
            'email' => $request->email,
            'name_company' => $request->name_company,
            'password' => Hash::make($request->password),
            'rol_idfk' => 1,
        ]);

        return redirect()->route('login')->with('success', 'Usuario registrado exitosamente.');
    }

        //Funcion que manda a llamar el formulario de inicio de sesion.
    public function showLogin()
    {
        return view('auth.login');
    }

    //Inicio de sesion
        //Funcion que procesa el formulario de inicio de sesion
    public function login(Request $request)
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
    }

        //Funcion que cierra la sesion del usuario
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
