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
        Schema::table('role_permission', function (Blueprint $table) {
            $table->foreign(['permission_idfk'], 'FK_roleperm_perm')->references(['permission_id'])->on('permission')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['rol_idfk'], 'FK_roleperm_role')->references(['rol_id'])->on('rol')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_permission', function (Blueprint $table) {
            $table->dropForeign('FK_roleperm_perm');
            $table->dropForeign('FK_roleperm_role');
        });
    }
};
