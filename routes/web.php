<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


//Rutas para el inicio de sesion y registro de usuario
Route::get('/', function () {
    return redirect()->route('register');
});


//Rutas para el inicio de sesion
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);


//Rutas para el registro de usuario
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);


//Ruta para el dashboard, solo accesible para usuarios autenticados
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');


//Ruta para cerrar sesion
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');