<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_integrations', function (Blueprint $table) {
            $table->id();

            /*
             * Datos internos de Punto.
             * No agregamos foreign keys todavía para evitar conflictos
             * con tipos exactos de columnas existentes.
             */
            $table->unsignedBigInteger('company_idfk')->nullable();
            $table->unsignedBigInteger('branch_idfk')->nullable();
            $table->unsignedBigInteger('userr_idfk')->nullable();

            /*
             * Sistema externo conectado.
             */
            $table->string('source_app', 50)->default('clientedigital');

            /*
             * Usuario externo de ClienteDigital.
             */
            $table->unsignedBigInteger('external_user_id')->nullable();

            /*
             * URL base de ClienteDigital.
             * Ejemplo:
             * http://localhost/clientedigital/index.php/apis
             */
            $table->string('external_base_url', 255);

            /*
             * Token real que ClienteDigital entregó al validar el código.
             * Punto lo usará para pedir productos y ventas.
             */
            $table->text('access_token');

            /*
             * Estado de la integración.
             * active, revoked, error, etc.
             */
            $table->string('status', 30)->default('active');

            /*
             * Fechas de sincronización.
             */
            $table->timestamp('last_products_sync_at')->nullable();
            $table->timestamp('last_sales_sync_at')->nullable();

            $table->timestamps();

            $table->index('company_idfk');
            $table->index('branch_idfk');
            $table->index('userr_idfk');
            $table->index('source_app');
            $table->index('external_user_id');
            $table->index('status');
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_integrations');
    }
};
