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
        Schema::create('payments', function (Blueprint $table) {
            $table->integer('payment_id', true);
            $table->dateTime('date_time');
            $table->string('payment_method', 70);
            $table->string('status_payment', 40);
            $table->decimal('commission', 19, 4);
            $table->integer('sale_idfk')->index('fk_payment_sale');
            $table->integer('customer_idfk')->index('fk_payment_customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
