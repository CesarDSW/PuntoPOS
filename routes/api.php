<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Integrations\ClienteDigitalIntegrationController;


// Integraciones (conexion entre Punto y ClienteDigital)
Route::prefix('integrations/clientedigital')->group(function () {
    Route::get('/', [ClienteDigitalIntegrationController::class, 'index']);
    Route::post('/connect', [ClienteDigitalIntegrationController::class, 'connect']);
    Route::post('/{integration}/sync-products', [ClienteDigitalIntegrationController::class, 'syncProducts']);
    Route::post('/{integration}/sync-sales', [ClienteDigitalIntegrationController::class, 'syncSales']);
});