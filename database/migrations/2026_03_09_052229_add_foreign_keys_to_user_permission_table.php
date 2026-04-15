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
        Schema::table('user_permission', function (Blueprint $table) {
            $table->foreign(['permission_idfk'], 'FK_userperm_perm')->references(['permission_id'])->on('permission')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['userr_idfk'], 'FK_userperm_user')->references(['userr_id'])->on('userr')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_permission', function (Blueprint $table) {
            $table->dropForeign('FK_userperm_perm');
            $table->dropForeign('FK_userperm_user');
        });
    }
};
