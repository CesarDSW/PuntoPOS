<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Route;
use App\Models\User;

use App\Support\UserAccess;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CurrentBranchController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DeveloperSupportController;
use App\Http\Controllers\GlobalSearchController;

// APIs / módulos
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
use App\Http\Controllers\Api\Integrations\ClienteDigitalIntegrationController;

/*
|--------------------------------------------------------------------------
| Ruta inicial
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Rutas de invitado
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    /*
    |--------------------------------------------------------------------------
    | Registro
    |--------------------------------------------------------------------------
    */
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register']);

    /*
    |--------------------------------------------------------------------------
    | Recuperación de contraseña
    |--------------------------------------------------------------------------
    */
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

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

    Route::get('/reset-password/{token}', function (string $token) {
        return view('auth.reset-password', [
            'token' => $token,
        ]);
    })->name('password.reset');

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

    /*
    |--------------------------------------------------------------------------
    | Google
    |--------------------------------------------------------------------------
    */
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle'])
        ->name('google.redirect');

    Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallBack'])
        ->name('google.callback');

    Route::get('/auth/google/test', [GoogleAuthController::class, 'fakeGoogleLogin'])
        ->name('google.test');
});

/*
|--------------------------------------------------------------------------
| Rutas autenticadas sin validar suscripción
|--------------------------------------------------------------------------
| Estas rutas deben funcionar aunque el usuario todavía no tenga una
| suscripción activa. Por eso NO llevan el middleware subscription.
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Sesión
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Integración ClienteDigital
    |--------------------------------------------------------------------------
    */
    Route::prefix('api/integrations/clientedigital')
        ->name('clientedigital.')
        ->group(function () {
            Route::get('/status', [ClienteDigitalIntegrationController::class, 'status'])
                ->name('status');

            Route::post('/connect', [ClienteDigitalIntegrationController::class, 'connect'])
                ->name('connect');

            Route::post('/{integration}/sync-products', [ClienteDigitalIntegrationController::class, 'syncProducts'])
                ->whereNumber('integration')
                ->name('sync.products');

            Route::post('/{integration}/sync-sales', [ClienteDigitalIntegrationController::class, 'syncSales'])
                ->whereNumber('integration')
                ->name('sync.sales');
        });

    /*
    |--------------------------------------------------------------------------
    | Developer / Soporte DEV
    |--------------------------------------------------------------------------
    */  
    Route::middleware('developer')
        ->prefix('dev')
        ->name('developer.')
        ->group(function () {
            Route::get('/soporte', [DeveloperSupportController::class, 'index'])
                ->name('support.index');
            
            Route::get('/soporte/{ticket}', [DeveloperSupportController::class, 'show'])
                ->whereNumber('ticket')
                ->name('support.show');

            Route::post('/soporte/{ticket}/responder', [DeveloperSupportController::class, 'reply'])
                ->whereNumber('ticket')
                ->name('support.reply');

            Route::post('soporte/{ticket}/cerrar', [DeveloperSupportController::class, 'close'])
                ->whereNumber('ticket')
                ->name('support.close');
        });

    /*
    |--------------------------------------------------------------------------
    | Suscripciones / Stripe
    |--------------------------------------------------------------------------
    */
    Route::get('/suscripcion', [StripeController::class, 'verSuscripcion'])
        ->name('suscripcion');

    Route::get('/suscripciones', function () {
        return redirect()->route('suscripcion');
    })->name('suscripciones');

    Route::get('/checkout/{plan}', [StripeController::class, 'checkout'])
        ->name('checkout');

    Route::post('/crear-suscripcion', [StripeController::class, 'crearSuscripcion'])
        ->name('crear.suscripcion');

    Route::get('/portal-cliente', [StripeController::class, 'portalCliente'])
        ->name('portal.cliente');

    /*
    |--------------------------------------------------------------------------
    | Soporte
    |--------------------------------------------------------------------------
    */
    Route::get('/support', [SupportController::class, 'index'])
        ->name('support.index');

    Route::post('/support-ticket', [SupportController::class, 'ticket'])
        ->name('support.ticket');

    Route::post('/support/{id}/completar', [SupportController::class, 'completar'])
        ->name('support.completar');

    Route::get('/support/conversaciones', [SupportController::class, 'myTickets'])
    ->name('support.conversations');

    Route::get('/support/conversaciones/{ticket}', [SupportController::class, 'conversation'])
        ->whereNumber('ticket')
        ->name('support.conversation');

    Route::post('/support/conversaciones/{ticket}/responder', [SupportController::class, 'replyUser'])
        ->whereNumber('ticket')
        ->name('support.conversation.reply');

    /*
    |--------------------------------------------------------------------------
    | Notificaciones
    |--------------------------------------------------------------------------
    */
    Route::get('/notificaciones/topbar', [NotificationController::class, 'topbar'])
        ->name('notifications.topbar');

    Route::post('/notificaciones/leer-todas', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read.all');

    Route::delete('/notificaciones/borrar-leidas', [NotificationController::class, 'deleteRead'])
        ->name('notifications.delete.read');

    Route::delete('/notificaciones/{id}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');

    Route::post('/notificaciones/{id}/leer', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas con suscripción activa
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'subscription'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'showDashboard'])
        ->name('dashboard');

    Route::post('/onboarding', [DashboardController::class, 'storeOnboarding'])
        ->name('onboarding.store');

    /* Route::get('/busqueda-global', [GlobalSearchController::class, 'search'])
        ->name('global-search'); */

    /*
    |--------------------------------------------------------------------------
    | Configuración
    |--------------------------------------------------------------------------
    */
    Route::get('/configuracion', [SettingsController::class, 'showSettings'])
        ->name('settings');

    Route::post('/configuracion', [SettingsController::class, 'updateSettings'])
        ->name('settings.update');

    Route::post('/configuracion/usuarios', [SettingsController::class, 'createUser'])
        ->name('users.store');

    Route::get('/configuracion/usuarios/{id}/editar', [SettingsController::class, 'editUser'])
        ->name('users.edit');

    Route::put('/configuracion/usuarios/{id}', [SettingsController::class, 'updateUser'])
        ->name('users.update');

    Route::delete('/configuracion/usuarios/{id}', [SettingsController::class, 'deleteUser'])
        ->name('users.delete');

    Route::post('/configuracion/notificaciones', [SettingsController::class, 'updateNotifications'])
        ->name('settings.notifications.update');

    Route::post('/configuracion/preferencias', [SettingsController::class, 'updatePreferences'])
        ->name('settings.preferences.update');

    Route::post('/configuracion/preferencias/reset', [SettingsController::class, 'resetPreferences'])
        ->name('settings.preferences.reset');

    Route::post('/password/update', [PasswordController::class, 'updatePassword'])
        ->name('settings.password.update');

    /*
    |--------------------------------------------------------------------------
    | Sucursal actual / creación de sucursal
    |--------------------------------------------------------------------------
    */
    Route::post('/sucursal-actual', [CurrentBranchController::class, 'update'])
        ->name('current-branch.update');

    Route::post('/sucursales', [SettingsController::class, 'storeBranch'])
        ->name('branches.store');

    /*
    |--------------------------------------------------------------------------
    | Clientes
    |--------------------------------------------------------------------------
    */
    Route::get('/cliente', [CustomerController::class, 'showCustomers'])
        ->name('customers');

    Route::post('/cliente', [CustomerController::class, 'storeCustomers'])
        ->name('customers.store');

    Route::get('/cliente/{id}/historial', [CustomerController::class, 'showCustomerHistory'])
        ->name('customers.history');

    Route::get('/cliente/{customerId}/ventas/{saleId}', [CustomerController::class, 'showCustomerSaleDetail'])
        ->whereNumber('customerId')
        ->whereNumber('saleId')
        ->name('customers.sales.show');

    Route::get('/cliente/{id}/editar', [CustomerController::class, 'editCustomer'])
        ->name('customers.edit');

    Route::put('/cliente/{id}', [CustomerController::class, 'updateCustomer'])
        ->name('customers.update');

    Route::delete('/cliente/{id}', [CustomerController::class, 'deleteCustomer'])
        ->name('customers.delete');

    Route::redirect('/clientes', '/cliente')
        ->name('customers.index');

    /*
    |--------------------------------------------------------------------------
    | Vistas de módulos
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Ventas
    |--------------------------------------------------------------------------
    */
    Route::get('/ventas', function () {
        abort_unless(UserAccess::has(auth()->user(), 'sales.view'), 403);

        return view('sales.index');
    })->name('sales.index');

    Route::get('/ventas/pos', function () {
        abort_unless(UserAccess::has(auth()->user(), 'sales.pos.use'), 403);

        return view('sales.pos');
    })->name('sales.pos');

    Route::get('/ventas/cajas', function () {
        abort_unless(UserAccess::has(auth()->user(), 'cash.history.view'), 403);

        return view('sales.cash-history');
    })->name('sales.cash.history');

    Route::get('/ventas/cajas/{id}', function (int $id) {
        abort_unless(UserAccess::has(auth()->user(), 'cash.history.view'), 403);

        return view('sales.cash-session-show', [
            'cashSessionId' => $id,
        ]);
    })->whereNumber('id')->name('sales.cash.show');

    Route::get('/ventas/{id}', function (int $id) {
        abort_unless(UserAccess::has(auth()->user(), 'sales.view'), 403);

        return view('sales.show', [
            'saleId' => $id,
        ]);
    })->whereNumber('id')->name('sales.show');

    Route::get('/ventas/{id}/ticket', function (int $id) {
        abort_unless(UserAccess::has(auth()->user(), 'sales.ticket.print'), 403);

        return view('sales.ticket', [
            'saleId' => $id,
        ]);
    })->whereNumber('id')->name('sales.ticket');

    Route::get('/ventas/{id}/factura', function (int $id) {
        abort_unless(UserAccess::has(auth()->user(), 'sales.view'), 403);

        return view('sales.invoice', [
            'saleId' => $id,
        ]);
    })->whereNumber('id')->name('sales.invoice');

    /*
    |--------------------------------------------------------------------------
    | Catálogo
    |--------------------------------------------------------------------------
    */
    Route::get('/catalogo', function () {
        abort_unless(UserAccess::has(auth()->user(), 'catalog.view'), 403);

        return view('catalog.index');
    })->name('catalog.index');

    /*
    |--------------------------------------------------------------------------
    | Inventario
    |--------------------------------------------------------------------------
    */
    Route::get('/inventario', function () {
        abort_unless(UserAccess::has(auth()->user(), 'inventory.view'), 403);

        return view('inventory.index');
    })->name('inventory.index');

    /*
    |--------------------------------------------------------------------------
    | Pagos
    |--------------------------------------------------------------------------
    */
    Route::view('/pagos', 'payments.index')
        ->name('payments.index');

    Route::get('/pagos/{id}', function (int $id) {
        return view('payments.show', [
            'paymentId' => $id,
        ]);
    })->whereNumber('id')->name('payments.show');

    /*
    |--------------------------------------------------------------------------
    | Reportes
    |--------------------------------------------------------------------------
    */
    Route::get('/reportes', function () {
        abort_unless(UserAccess::has(auth()->user(), 'reports.view'), 403);

        return view('reports.index');
    })->name('reports.index');

    /*
    |--------------------------------------------------------------------------
    | Factura
    |--------------------------------------------------------------------------
    */
    Route::get('/factura/{id}', [FacturaController::class, 'generar'])
        ->whereNumber('id')
        ->name('factura.generar');

    /*
    |--------------------------------------------------------------------------
    | APIs internas
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Contexto de sucursal
        |--------------------------------------------------------------------------
        */
        Route::prefix('branches')->group(function () {
            Route::get('/', [BranchContextController::class, 'index']);
            Route::get('/current', [BranchContextController::class, 'current']);
            Route::post('/current', [BranchContextController::class, 'update']);
        });

        /*
        |--------------------------------------------------------------------------
        | Inventario
        |--------------------------------------------------------------------------
        */
        Route::prefix('inventory')->group(function () {
            Route::get('/summary', [InventoryController::class, 'summary']);
            Route::get('/low-stock', [InventoryController::class, 'lowStock']);
            Route::get('/products/{productId}', [InventoryController::class, 'show']);
            Route::get('/reasons', [InventoryController::class, 'reasons']);
            Route::get('/', [InventoryController::class, 'index']);

            Route::prefix('adjustments')->group(function () {
                Route::get('/', [InventoryAdjustmentController::class, 'index']);
                Route::post('/', [InventoryAdjustmentController::class, 'store']);
                Route::post('/bulk', [InventoryAdjustmentController::class, 'bulkStore']);
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Catálogo
        |--------------------------------------------------------------------------
        */
        Route::prefix('catalog')->group(function () {
            Route::get('/summary', [CatalogController::class, 'summary']);
            Route::get('/items', [CatalogController::class, 'items']);

            Route::prefix('bulk-upload')->group(function () {
                Route::get('/template', [CatalogBulkUploadController::class, 'template']);
                Route::post('/', [CatalogBulkUploadController::class, 'upload']);
            });
        });

        Route::prefix('products')->group(function () {
            Route::get('/{id}', [ProductController::class, 'show']);
            Route::post('/', [ProductController::class, 'store']);
            Route::put('/{id}', [ProductController::class, 'update']);
            Route::patch('/{id}/deactivate', [ProductController::class, 'deactivate']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
        });

        Route::prefix('services')->group(function () {
            Route::get('/{id}', [ServiceController::class, 'show']);
            Route::post('/', [ServiceController::class, 'store']);
            Route::put('/{id}', [ServiceController::class, 'update']);
            Route::patch('/{id}/deactivate', [ServiceController::class, 'deactivate']);
            Route::delete('/{id}', [ServiceController::class, 'destroy']);
        });

        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::get('/{id}', [CategoryController::class, 'show']);
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{id}', [CategoryController::class, 'update']);
            Route::patch('/{id}/deactivate', [CategoryController::class, 'deactivate']);
            Route::delete('/{id}', [CategoryController::class, 'destroy']);
        });

        /*
        |--------------------------------------------------------------------------
        | Ventas / POS / Caja / Turnos
        |--------------------------------------------------------------------------
        */
        Route::prefix('sales')->group(function () {
            Route::get('/summary', [SalesController::class, 'summary']);
            Route::get('/', [SalesController::class, 'index']);
            Route::post('/', [SalesController::class, 'store']);

            Route::post('/pending', [SalesController::class, 'storePending']);
            Route::get('/{id}', [SalesController::class, 'show'])->whereNumber('id');
            Route::post('/{id}/confirm', [SalesController::class, 'confirmPending'])->whereNumber('id');
            Route::post('/{id}/cancel', [SalesController::class, 'cancelPending'])->whereNumber('id');

            Route::prefix('pos')->group(function () {
                Route::get('/status', [PosController::class, 'status']);
                Route::get('/products', [PosController::class, 'products']);
                Route::get('/customers', [PosController::class, 'customers']);
            });

            Route::prefix('cash')->group(function () {
                Route::post('/open', [CashRegisterController::class, 'open']);
                Route::post('/close', [CashRegisterController::class, 'close']);
                Route::get('/history', [CashRegisterController::class, 'history']);
                Route::get('/{id}', [CashRegisterController::class, 'show'])->whereNumber('id');
            });

            Route::prefix('shifts')->group(function () {
                Route::post('/open', [ShiftController::class, 'open']);
                Route::post('/close', [ShiftController::class, 'close']);
                Route::get('/summary', [ShiftController::class, 'summary']);
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Pagos
        |--------------------------------------------------------------------------
        */
        Route::prefix('payments')->group(function () {
            Route::get('/summary', [PaymentController::class, 'summary']);
            Route::get('/export', [PaymentController::class, 'export']);
            Route::get('/{id}', [PaymentController::class, 'show'])->whereNumber('id');
            Route::get('/', [PaymentController::class, 'index']);
        });

        /*
        |--------------------------------------------------------------------------
        | Reportes
        |--------------------------------------------------------------------------
        */
        Route::prefix('reports')->group(function () {
            Route::get('/summary', [ReportController::class, 'summary']);
            Route::get('/sales-vs-costs', [ReportController::class, 'salesVsCosts']);
            Route::get('/categories', [ReportController::class, 'categories']);
            Route::get('/peak-hours', [ReportController::class, 'peakHours']);
            Route::get('/top-products', [ReportController::class, 'topProducts']);
            Route::get('/payment-methods', [ReportController::class, 'paymentMethods']);
        });
    });
});