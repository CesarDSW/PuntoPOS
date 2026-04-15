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
        Schema::create('sale', function (Blueprint $table) {
            $table->integer('sale_id', true);
            $table->dateTime('date_time')->useCurrent();
            $table->integer('company_idfk');
            $table->integer('branch_idfk');
            $table->integer('cashier_userr_idfk')->index('fk_sale_cashier');
            $table->integer('customer_idfk')->index('fk_sale_customer');
            $table->decimal('subtotal', 19, 4)->default(0);
            $table->decimal('discount', 19, 4)->default(0);
            $table->decimal('total', 19, 4)->default(0);
            $table->string('status_sale', 20)->default('PAGADA');

            $table->index(['branch_idfk', 'date_time'], 'ix_sale_branch_date');
            $table->index(['company_idfk', 'date_time'], 'ix_sale_company_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale');
    }
};
