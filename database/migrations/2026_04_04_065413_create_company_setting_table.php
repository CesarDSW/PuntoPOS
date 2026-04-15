<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_setting', function (Blueprint $table) {
            $table->integer('company_setting_id', true);
            $table->integer('company_idfk')->unique();

            //Notificaciónes
            $table->boolean('notify_low_stock')->default(true);
            $table->boolean('notify_sale_cancelled')->default(true);
            $table->boolean('notify_out_of_stock')->default(true);

            //Preferencias regionales
            $table->string('language', 50)->default('Español (México)');
            $table->string('timezone', 100)->default('Ciudad de México (GMT-6)');
            $table->string('date_format', 30)->default('DD/MM/YYYY');
            $table->string('time_format', 20)->default('24 horas');

            //Preferencias de impresion
            $table->boolean('auto_print')->default(true);
            $table->boolean('show_taxes')->default(true);
            $table->string('printer_width', 10)->default('80mm');

            //Preferencias visuales
            $table->string('theme', 20)->default('Claro');
            $table->string('price_decimals', 20)->default('2');

            $table->foreign('company_idfk')->references('company_id')->on('company')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_setting');
    }
};
