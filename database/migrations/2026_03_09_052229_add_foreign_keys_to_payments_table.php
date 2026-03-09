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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign(['customer_idfk'], 'FK_payment_customer')->references(['customer_id'])->on('customer')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['sale_idfk'], 'FK_payment_sale')->references(['sale_id'])->on('sale')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('FK_payment_customer');
            $table->dropForeign('FK_payment_sale');
        });
    }
};
