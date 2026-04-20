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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();

            // Relación con empresa
            $table->unsignedBigInteger('company_idfk');

            // Preferencias
            $table->string('theme')->default('Claro');
            $table->integer('price_decimals')->default(2);

            $table->timestamps();

            // (Opcional pero recomendado) llave foránea
            // $table->foreign('company_idfk')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};