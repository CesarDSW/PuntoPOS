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
        Schema::create('saleitem', function (Blueprint $table) {
            $table->integer('saleitem_id', true);
            $table->integer('sale_idfk')->index('ix_saleitem_sale');
            $table->string('item_type', 10);
            $table->integer('product_idfk')->nullable()->index('fk_saleitem_product');
            $table->integer('service_idfk')->nullable()->index('fk_saleitem_service');
            $table->integer('amount');
            $table->decimal('unit_price', 19, 4);
            $table->decimal('discount', 19, 4)->default(0);
            $table->decimal('total_line', 19, 4)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saleitem');
    }
};
