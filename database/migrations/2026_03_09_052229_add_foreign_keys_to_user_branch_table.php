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
        Schema::table('user_branch', function (Blueprint $table) {
            $table->foreign(['branch_idfk'], 'FK_user_branch_branch')->references(['branch_id'])->on('branch')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['userr_idfk'], 'FK_user_branch_user')->references(['userr_id'])->on('userr')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_branch', function (Blueprint $table) {
            $table->dropForeign('FK_user_branch_branch');
            $table->dropForeign('FK_user_branch_user');
        });
    }
};
