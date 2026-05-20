<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->integer('customer_id', true);
            $table->string('name_customer', 150);
            $table->string('phone', 10);
            $table->string('email', 320);
            $table->integer('company_idfk')->index('idx_customer_company_idfk');
            $table->boolean('status_customer')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};