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
        Schema::table('inventory_movement', function (Blueprint $table) {
            $table->foreign(['company_idfk'], 'FK_invmov_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['destination_branch_idfk'], 'FK_invmov_destination_branch')->references(['branch_id'])->on('branch')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['origin_branch_idfk'], 'FK_invmov_origin_branch')->references(['branch_id'])->on('branch')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['userr_idfk'], 'FK_invmov_user')->references(['userr_id'])->on('userr')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_movement', function (Blueprint $table) {
            $table->dropForeign('FK_invmov_company');
            $table->dropForeign('FK_invmov_destination_branch');
            $table->dropForeign('FK_invmov_origin_branch');
            $table->dropForeign('FK_invmov_user');
        });
    }
};
