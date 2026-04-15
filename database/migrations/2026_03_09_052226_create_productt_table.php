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
        Schema::create('productt', function (Blueprint $table) {
            $table->integer('product_id', true);
            $table->string('name_product', 80);
            $table->string('code_product', 15);
            $table->string('description_product', 250)->nullable();
            $table->decimal('price', 19, 4);
            $table->boolean('status_product')->nullable();
            $table->integer('company_idfk');
            $table->integer('category_idfk')->index('fk_product_category');

            $table->unique(['company_idfk', 'code_product'], 'ux_product_company_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productt');
    }
};
