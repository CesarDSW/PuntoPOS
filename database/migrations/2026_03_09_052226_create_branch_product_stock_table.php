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
        Schema::create('branch_product_stock', function (Blueprint $table) {
            $table->integer('branch_idfk');
            $table->integer('product_idfk')->index('fk_bps_product');
            $table->integer('stocks')->default(0);
            $table->integer('minimum_stock')->nullable();
            $table->boolean('status_stock')->default(true);

            $table->primary(['branch_idfk', 'product_idfk']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_product_stock');
    }
};
