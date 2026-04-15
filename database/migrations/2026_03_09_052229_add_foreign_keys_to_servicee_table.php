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
        Schema::table('servicee', function (Blueprint $table) {
            $table->foreign(['category_idfk'], 'FK_service_category')->references(['category_id'])->on('category')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['company_idfk'], 'FK_service_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicee', function (Blueprint $table) {
            $table->dropForeign('FK_service_category');
            $table->dropForeign('FK_service_company');
        });
    }
};
