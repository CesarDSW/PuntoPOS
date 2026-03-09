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
        Schema::table('userr', function (Blueprint $table) {
            $table->foreign(['company_idfk'], 'FK_userr_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['rol_idfk'], 'FK_userr_rol')->references(['rol_id'])->on('rol')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('userr', function (Blueprint $table) {
            $table->dropForeign('FK_userr_company');
            $table->dropForeign('FK_userr_rol');
        });
    }
};
