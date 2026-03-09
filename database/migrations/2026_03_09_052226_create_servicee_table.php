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
        Schema::create('servicee', function (Blueprint $table) {
            $table->integer('service_id', true);
            $table->string('name_service', 80);
            $table->string('description_service', 250)->nullable();
            $table->decimal('price', 19, 4);
            $table->boolean('status_service')->default(true);
            $table->integer('company_idfk')->index('fk_service_company');
            $table->integer('category_idfk')->nullable()->index('fk_service_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicee');
    }
};
