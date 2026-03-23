<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


//Rutas para el inicio de sesion y registro de usuario
Route::get('/', function () {
    return redirect()->route('login');
});


//Rutas para el inicio de sesion
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);


//Rutas para el registro de usuario
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);


//Ruta para el dashboard, solo accesible para usuarios autenticados
Route::get('/dashboard', function () {
    $user = auth()->user();

    $showOnboarding = false;
    
    if ($user && $user->company_idfk) {
        $company = \App\Models\Company::find($user->company_idfk);
        
        if ($company && !$company->onboarding_completed) {
            $showOnboarding = true;
        }
    }
    return view('dashboard', compact('showOnboarding'));
})->middleware('auth')->name('dashboard');

//Ruta para mandar a llamar el onboarding (ventana para registrar datos) en el dashboard
Route::post('/onboarding', [AuthController::class, 'storeOnboarding'])
->middleware('auth')
->name('onboarding.store');

//Configuracion
//Ruta para entrar en configuracion desde el dashboard
Route::get('/configuracion',[AuthController::class, 'showSettings'])
->middleware('auth')
->name('settings');

//Ruta para que en configuracion se puedan editar o agregar datos
Route::post('/configuracion',[AuthController::class, 'updateSettings'])
->middleware('auth')
->name('settings.update');

//Ruta para configurar los usuarios accesibles al sistema
ROUTE::post('/configuración/usuarios', [AuthController::class, 'createUser'])
->middleware('auth')
->name('users.store');

//Ruta para cambiar la contraseña del usuario
Route::post('/password/update', [AuthController::class, 'updatePassword'])
->middleware('auth')
->name('password.update');

//Clientes
//Ruta para entrar a la pagina de clientes desde el dashboard
Route::get('/cliente', [AuthController::class, 'showCustomers'])
->middleware('auth')
->name('customers');

//Ruta para crear nuevos clientes
Route::post('/cliente', [AuthController::class, 'storeCustomers'])
->middleware('auth')
->name('customers.store');

//Ruta para cerrar sesion
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');