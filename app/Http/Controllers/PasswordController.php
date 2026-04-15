<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /*Recuperacion de contraseña*/
    //Funcion que actualiza la contraseña
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if(Hash::check($request->new_password, $user->password)){
            return back()->withErrors([
                'new_password' => 'La nueva contraseña no puede ser igual a la actual.'
            ]);
        }

        if(!Hash::check($request->current_password, $user->password)){
            return back()->withErrors([
                'current_password' => 'La contraseña actual es incorrecta.'
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success_password', 'Contraseña actualizada correctamente.');
    }
}
