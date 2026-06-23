<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;


class GoogleAuthController extends Controller
{
    /*Registro mediante google*/
    //Funcion para redirigir a la pagina de google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
        ->scopes(['openid', 'profile', 'email'])
        ->redirect();
    }

    //Funcion para, cuando el usuario ingrese su cuenta de google, continue con el registro o que inicie sesion.
    public function handleGoogleCallBack()
    {
        try{
            $googleUser = Socialite::driver('google')->user();

            $email = $googleUser->getEmail();
            $name = $googleUser->getName();
            $googleId = $googleUser->getId();

            $existingUser = User::where('email', $email)->first();

            //Si ya existe en el sistema, inicia sesión
            if ($existingUser){
                if(!$existingUser->google_id){
                    $existingUser->google_id = $googleId;
                    $existingUser->google_email = $email;
                    $existingUser->save();
                }

                Auth::login($existingUser);

                if ($existingUser->isDeveloper()) {
                    return redirect()->route('developer.support.index');
                }

                return redirect()->route('dashboard');
            }
            
            //Si no existe, guardar datos temporales para precargar registro
            Session::put('google_user', [
                'google_id' => $googleId,
                'google_email' => $email,
                'name_user' => $name,
            ]);

            return redirect()->route('register')->with('info', 'Completa tu registro para continuar.');
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'google' => 'No se pudo iniciar sesión con Google.'
            ]);
        }
    }


    //prueba
    public function fakeGoogleLogin(){
        $googleUser = [
            'google_id' => 'google_dev_12345',
            'google_email' => 'dev@punto.com',
            'name_user' => 'Desarrollador Punto',
        ];

        $existingUser = User::where('email', $googleUser['google_email'])->first();

        if($existingUser){
            if(!$existingUser->google_id){
                $existingUser->google_id = $googleUser['google_id'];
                $existingUser->google_email = $googleUser['google_email'];
                $existingUser->save();
            }

            Auth::login($existingUser);

            if ($existingUser->isDeveloper()) {
                return redirect()->route('developer.support.index');
            }

            return redirect()->route('dashboard');
        }
        
        //Si no existe, guardar datos temporales para precargar registro
        Session::put('google_user', $googleUser);
        return redirect()->route('register')->with('info', 'Completa tu registro para continuar.');
    }
}
