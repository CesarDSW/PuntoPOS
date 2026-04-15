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
        Schema::create('inventory_movement_item', function (Blueprint $table) {
            $table->integer('invmov_idfk');
            $table->integer('product_idfk')->index('fk_invmov_item_product');
            $table->integer('amount');

            $table->primary(['invmov_idfk', 'product_idfk']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movement_item');
    }
};
