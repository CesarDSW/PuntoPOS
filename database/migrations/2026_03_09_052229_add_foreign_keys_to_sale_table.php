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
        Schema::table('sale', function (Blueprint $table) {
            $table->foreign(['branch_idfk'], 'FK_sale_branch')->references(['branch_id'])->on('branch')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['cashier_userr_idfk'], 'FK_sale_cashier')->references(['userr_id'])->on('userr')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['company_idfk'], 'FK_sale_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['customer_idfk'], 'FK_sale_customer')->references(['customer_id'])->on('customer')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale', function (Blueprint $table) {
            $table->dropForeign('FK_sale_branch');
            $table->dropForeign('FK_sale_cashier');
            $table->dropForeign('FK_sale_company');
            $table->dropForeign('FK_sale_customer');
        });
    }
};
