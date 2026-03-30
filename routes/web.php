<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Route;

use App\Models\User;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CustomerController;

//Adrian
/*Autenticación*/
//Rutas para el inicio de sesion y registro de usuario
Route::get('/', function () {
    return redirect()->route('login');
});

//Adrian
//Rutas para el inicio de sesion
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
//Route::post('/login', [AuthController::class, 'login']);

//Adrian
//Rutas para el registro de usuario
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

//Adrian
//Ruta para cerrar sesion
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

//Adrian
/*Recuperar contraseña*/
//Ruta para cambiar la contraseña si al usuario se le olvida
    //Formulario para pedir el enlace
Route::get('/forgot-password', function() {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');

//Adrian
//Ruta para enviar un enlace por correo al usuario
Route::post('/forgot-password', function(Request $request){
    $request->validate([
        'email' => 'required|email',
    ]);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
    ? back()->with(['status' => __($status)])
    : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

//Adrian
//Ruta para redirigir al usuario al formulario para nueva contraseña
Route::get('/reset-password/{token}', function(string $token){
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

//Adrian
//Ruta para guardar nueva contraseña
Route::post('/reset-password', function(Request $request){
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user, string $password){
            $user->forceFill([
                'password' => Hash::make($password),
            ]);

            $user->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
    ? redirect()->route('login')->with('status', __($status))
    : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');

//Adrian
/*Google*/
//Rutas para registrar la cuenta con cuenta de google
Route::get('/auth/google/redirect', [GoogleController::class, 'redirectToGoogle'])
->middleware('guest')
->name('google.redirect');

Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallBack'])
->middleware('guest')
->name('google.callback');

//prueba de google
Route::get('/auth/google/test', [GoogleController::class, 'fakeGoogleLogin'])
->middleware('guest')
->name('google.test');

//Adrian
//Rutas protegidas
Route::middleware('auth')->group(function (){

    //Adrian
    /*Dashboard*/
    //Ruta para el dashboard, solo accesible para usuarios autenticados
    Route::get('/dashboard', [DashboardController::class, 'showDashboard'])->name('dashboard');

    //Adrian
    //Ruta para mandar a llamar el onboarding (ventana para registrar datos) en el dashboard
    Route::post('/onboarding', [DashboardController::class, 'storeOnboarding'])->name('onboarding.store');

    //Adrian
    /*Configuracion*/
    //Ruta para entrar en configuracion desde el dashboard
    Route::get('/configuracion',[SettingsController::class, 'showSettings'])->name('settings');

    //Adrian
    //Ruta para que en configuracion se puedan editar o agregar datos
    Route::post('/configuracion',[SettingsController::class, 'updateSettings'])->name('settings.update');

    //Adrian
    //Ruta para configurar los usuarios accesibles al sistema
    Route::post('/configuracion/usuarios', [SettingsController::class, 'createUser'])->name('users.store');

    //Adrian
    //Ruta para cambiar la contraseña del usuario
    Route::post('/password/update', [PasswordController::class, 'updatePassword'])->name('password.update');

    //Adrian
    /*Clientes*/
    //Ruta para entrar a la pagina de clientes desde el dashboard
    Route::get('/cliente', [CustomerController::class, 'showCustomers'])->name('customers');

    //Adrian
    //Ruta para crear nuevos clientes
    Route::post('/cliente', [CustomerController::class, 'storeCustomers'])->name('customers.store');

    //Adrian
    //Ruta para ver el historial del cliente
    Route::get('/cliente/{id}/historial', [CustomerController::class, 'showCustomerHistory'])->name('customers.history');
    
    //Adrian
    //Ruta para editar los clientes
    Route::get('/cliente/{id}/editar', [CustomerController::class, 'editCustomer'])->name('customers.edit');

    //Adrian
    //Ruta para actualizar el cliente una vez editado
    Route::put('/cliente/{id}', [CustomerController::class, 'updateCustomer'])->name('customers.update');

    //Adrian
    //Ruta para borrar el cliente de la tabla
    Route::delete('/cliente/{id}', [CustomerController::class, 'deleteCustomer'])->name('customers.delete');
});