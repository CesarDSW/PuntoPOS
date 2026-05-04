<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Route;
use App\Models\User;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CustomerController;

///daniel
use App\Http\Controllers\Api\BranchContextController;
use App\Http\Controllers\Api\Inventory\InventoryController;
use App\Http\Controllers\Api\Inventory\InventoryAdjustmentController;
use App\Http\Controllers\Api\Catalogo\CatalogController;
use App\Http\Controllers\Api\Catalogo\ProductController;
use App\Http\Controllers\Api\Catalogo\ServiceController;
use App\Http\Controllers\Api\Catalogo\CategoryController;
use App\Http\Controllers\Api\Catalogo\CatalogBulkUploadController;
use App\Http\Controllers\Api\Ventas\SalesController;
use App\Http\Controllers\Api\Ventas\PosController;
use App\Http\Controllers\Api\Ventas\CashRegisterController;
use App\Http\Controllers\Api\Ventas\ShiftController;
use App\Http\Controllers\Api\Payments\PaymentController;
use App\Http\Controllers\Api\Reports\ReportController;

// Adrian
// Rutas para el inicio de sesion y registro de usuario
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|-----------------------------------------------------------------------
| Rutas de invitado
|-----------------------------------------------------------------------
*/
Route::middleware('guest')->group(function(){
    // Inicio de sesion
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    // Route::post('/login', [AuthController::class, 'login']);

    // Registro
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

        // Recuperacion de contraseña
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    
    })->name('password.request');
        
        // Ruta para enviar un enlace por correo al usuario
    Route::post('/forgot-password', function (Request $request) {
        $request->validate([
            'email' => 'required|email',
        ]);
        
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    })->name('password.email');
        
        // Ruta para redirigir al usuario al formulario para nueva contraseña
    Route::get('/reset-password/{token}', function (string $token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');

        // Ruta para guardar nueva contraseña
    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
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
    })->name('password.update');

    //Google
        // Rutas para registrar la cuenta con cuenta de google
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle'])
        ->name('google.redirect');

    Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallBack'])
        ->name('google.callback');

        // Prueba de google
    Route::get('/auth/google/test', [GoogleAuthController::class, 'fakeGoogleLogin'])
        ->name('google.test');
});

/*
|-----------------------------------------------------------------------
| Rutas protegidas
|-----------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    //Cerrar sesion
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    //Dashboard
        // Ruta para el dashboard, solo accesible para usuarios autenticados
    Route::get('/dashboard', [DashboardController::class, 'showDashboard'])->name('dashboard');
        // Ruta para mandar a llamar el onboarding (ventana para registrar datos) en el dashboard
    Route::post('/onboarding', [DashboardController::class, 'storeOnboarding'])->name('onboarding.store');

    //Configuracion
        // Ruta para entrar en configuracion desde el dashboard
    Route::get('/configuracion', [SettingsController::class, 'showSettings'])->name('settings');
        // Ruta para que en configuracion se puedan editar o agregar datos
    Route::post('/configuracion', [SettingsController::class, 'updateSettings'])->name('settings.update');
        // Ruta para configurar los usuarios accesibles al sistema
    Route::post('/configuracion/usuarios', [SettingsController::class, 'createUser'])->name('users.store');
        //Rutas para modificar o borrar usuarios
    Route::put('/configuracion/usuarios/{id}', [SettingsController::class, 'updateUser'])->name('users.update');
    Route::get('/configuracion/usuarios/{id}/editar', [SettingsController::class, 'editUser'])->name('users.edit');
    Route::delete('/configuracion/usuarios/{id}', [SettingsController::class, 'deleteUser'])->name('users.delete');
        //Ruta para las notificaciones 
    Route::post('/configuracion/notificaciones', [SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
        //Rutas para configurar las preferencias
    Route::post('/configuracion/preferencias', [SettingsController::class, 'updatePreferences'])->name('settings.preferences.update');
    Route::post('/configuracion/preferencias/reset', [SettingsController::class, 'resetPreferences'])->name('settings.preferences.reset');
        // Ruta para cambiar la contraseña del usuario
    Route::post('/password/update', [PasswordController::class, 'updatePassword'])->name('settings.password.update');

    //Clientes
        // Ruta para entrar a la pagina de clientes desde el dashboard
    Route::get('/cliente', [CustomerController::class, 'showCustomers'])->name('customers');
        // Ruta para crear nuevos clientes
    Route::post('/cliente', [CustomerController::class, 'storeCustomers'])->name('customers.store');
        // Ruta para ver el historial del cliente
    Route::get('/cliente/{id}/historial', [CustomerController::class, 'showCustomerHistory'])->name('customers.history');
        // Ruta para editar los clientes
    Route::get('/cliente/{id}/editar', [CustomerController::class, 'editCustomer'])->name('customers.edit');
        // Ruta para actualizar el cliente una vez editado
    Route::put('/cliente/{id}', [CustomerController::class, 'updateCustomer'])->name('customers.update');
        // Ruta para borrar el cliente de la tabla
    Route::delete('/cliente/{id}', [CustomerController::class, 'deleteCustomer'])->name('customers.delete');
    Route::redirect('/clientes', '/cliente')->name('customers.index');

    /*Daniel*/
    // Vistas del modulo POS
    Route::view('/ventas', 'sales.index')->name('sales.index');
    Route::view('/ventas/pos', 'sales.pos')->name('sales.pos');
    
    // Historial de cajas
    Route::view('/ventas/cajas', 'sales.cash-history')->name('sales.cash.history');
    
    Route::get('/ventas/cajas/{id}', function (int $id) {
        return view('sales.cash-session-show', [
            'cashSessionId' => $id,
        ]);
    })->whereNumber('id')->name('sales.cash.show');

    // Detalle de venta
    Route::get('/ventas/{id}', function (int $id) {
        return view('sales.show', ['saleId' => $id]);
    })->whereNumber('id')->name('sales.show');

    //Ticket --Adrian
    Route::get('/ventas/{id}/ticket', function (int $id){
        return view('sales.ticket', ['saleId' => $id]);
    })->whereNumber('id')->name('sales.ticket');

    Route::view('/catalogo', 'catalog.index')->name('catalog.index');
    Route::view('/inventario', 'inventory.index')->name('inventory.index');
    Route::view('/pagos', 'payments.index')->name('payments.index');
    
    // Detalle de pago
    Route::get('/pagos/{id}', function (int $id) {
        return view('payments.show', ['paymentId' => $id]);
    })->whereNumber('id')->name('payments.show');
    Route::view('/reportes', 'reports.index')->name('reports.index');

    // APIs internas protegidas
    Route::prefix('api')->group(function () {
        // Contexto de sucursal
        Route::get('/branches', [BranchContextController::class, 'index']);
        Route::get('/branches/current', [BranchContextController::class, 'current']);
        Route::post('/branches/current', [BranchContextController::class, 'update']);

        // Inventario
        Route::get('/inventory/summary', [InventoryController::class, 'summary']);
        Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock']);
        Route::get('/inventory', [InventoryController::class, 'index']);
        Route::get('/inventory/products/{productId}', [InventoryController::class, 'show']);
        Route::get('/inventory/reasons', [InventoryController::class, 'reasons']);

        // Ajustes de inventario
        Route::get('/inventory/adjustments', [InventoryAdjustmentController::class, 'index']);
        Route::post('/inventory/adjustments', [InventoryAdjustmentController::class, 'store']);
        Route::post('/inventory/adjustments/bulk', [InventoryAdjustmentController::class, 'bulkStore']);

        // Catálogo
        Route::get('/catalog/summary', [CatalogController::class, 'summary']);
        Route::get('/catalog/items', [CatalogController::class, 'items']);

        // Productos
        Route::get('/products/{id}', [ProductController::class, 'show']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::patch('/products/{id}/deactivate', [ProductController::class, 'deactivate']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        // Servicios
        Route::get('/services/{id}', [ServiceController::class, 'show']);
        Route::post('/services', [ServiceController::class, 'store']);
        Route::put('/services/{id}', [ServiceController::class, 'update']);
        Route::patch('/services/{id}/deactivate', [ServiceController::class, 'deactivate']);
        Route::delete('/services/{id}', [ServiceController::class, 'destroy']);

        // Categorías
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/{id}', [CategoryController::class, 'show']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::patch('/categories/{id}/deactivate', [CategoryController::class, 'deactivate']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // Carga masiva
        Route::get('/catalog/bulk-upload/template', [CatalogBulkUploadController::class, 'template']);
        Route::post('/catalog/bulk-upload', [CatalogBulkUploadController::class, 'upload']);

        // Ventas
        Route::get('/sales/summary', [SalesController::class, 'summary']);
        Route::get('/sales', [SalesController::class, 'index']);
        Route::get('/sales/{id}', [SalesController::class, 'show'])->whereNumber('id');
        Route::post('/sales', [SalesController::class, 'store']);

        // POS
        Route::get('/sales/pos/status', [PosController::class, 'status']);
        Route::get('/sales/pos/products', [PosController::class, 'products']);
        Route::get('/sales/pos/customers', [PosController::class, 'customers']);

        // Caja
        Route::post('/sales/cash/open', [CashRegisterController::class, 'open']);
        Route::post('/sales/cash/close', [CashRegisterController::class, 'close']);
        Route::get('/sales/cash/history', [CashRegisterController::class, 'history']);
        Route::get('/sales/cash/{id}', [CashRegisterController::class, 'show'])->whereNumber('id');

        // Turnos
        Route::post('/sales/shifts/open', [ShiftController::class, 'open']);
        Route::post('/sales/shifts/close', [ShiftController::class, 'close']);
        Route::get('/sales/shifts/summary', [ShiftController::class, 'summary']);

        // Pagos
        Route::get('/payments/summary', [PaymentController::class, 'summary']);
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/export', [PaymentController::class, 'export']);
        Route::get('/payments/{id}', [PaymentController::class, 'show'])->whereNumber('id');

        // Reportes
        Route::get('/reports/summary', [ReportController::class, 'summary']);
        Route::get('/reports/sales-vs-costs', [ReportController::class, 'salesVsCosts']);
        Route::get('/reports/categories', [ReportController::class, 'categories']);
        Route::get('/reports/peak-hours', [ReportController::class, 'peakHours']);
        Route::get('/reports/top-products', [ReportController::class, 'topProducts']);
        Route::get('/reports/payment-methods', [ReportController::class, 'paymentMethods']);
    });
});