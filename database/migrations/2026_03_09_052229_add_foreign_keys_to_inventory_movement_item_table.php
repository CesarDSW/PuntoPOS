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
        Schema::table('inventory_movement_item', function (Blueprint $table) {
            $table->foreign(['invmov_idfk'], 'FK_invmov_item_mov')->references(['invmov_id'])->on('inventory_movement')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['product_idfk'], 'FK_invmov_item_product')->references(['product_id'])->on('productt')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_movement_item', function (Blueprint $table) {
            $table->dropForeign('FK_invmov_item_mov');
            $table->dropForeign('FK_invmov_item_product');
        });
    }
};
