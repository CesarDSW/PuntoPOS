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
        Schema::table('saleitem', function (Blueprint $table) {
            $table->foreign(['product_idfk'], 'FK_saleitem_product')->references(['product_id'])->on('productt')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['sale_idfk'], 'FK_saleitem_sale')->references(['sale_id'])->on('sale')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['service_idfk'], 'FK_saleitem_service')->references(['service_id'])->on('servicee')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saleitem', function (Blueprint $table) {
            $table->dropForeign('FK_saleitem_product');
            $table->dropForeign('FK_saleitem_sale');
            $table->dropForeign('FK_saleitem_service');
        });
    }
};
