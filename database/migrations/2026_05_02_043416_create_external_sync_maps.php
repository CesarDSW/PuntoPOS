<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('external_sync_maps', function (Blueprint $table) {
            $table->id();
            
            /*
             * Relación con la integración externa.
            */
            $table->unsignedBigInteger('external_integration_id');

            /*
             * Tipo de entidad sincronizada.
             * Ejemplo: 
             * product
             * category
             * sale
             * sale_item
             */
            $table->string('entity_type', 50);

            /*
             * ID del registro en ClienteDigital.
             */
            $table->string('external_id', 100);

            /*
             * Tabla local en Punto.
             * Ejemplos:
             * productt
             * category
             * sale
             */
            $table->string('local_table', 100);

            /*
             * ID del registro creado en Punto.
             */
            $table->unsignedBigInteger('local_id');

            $table->timestamps();

            $table->index('external_integration_id');
            $table->index('entity_type');
            $table->index('external_id');
            $table->index('local_table');
            $table->index('local_id');

            /*
             * Esto evita duplicados.
             * Por ejemplo:
             * ClienteDigital product 1 solo puede relacionarse una vez
             * con un producto de Punto.
             */
             $table->unique(
                ['external_integration_id', 'entity_type', 'external_id'],
                'external_sync_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_sync_maps');
    }
};
