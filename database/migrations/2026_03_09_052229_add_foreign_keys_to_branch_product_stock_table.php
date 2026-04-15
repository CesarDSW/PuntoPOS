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
        Schema::table('branch_product_stock', function (Blueprint $table) {
            $table->foreign(['branch_idfk'], 'FK_bps_branch')->references(['branch_id'])->on('branch')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['product_idfk'], 'FK_bps_product')->references(['product_id'])->on('productt')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_product_stock', function (Blueprint $table) {
            $table->dropForeign('FK_bps_branch');
            $table->dropForeign('FK_bps_product');
        });
    }
};
