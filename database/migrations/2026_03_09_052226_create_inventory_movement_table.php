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
        Schema::create('inventory_movement', function (Blueprint $table) {
            $table->integer('invmov_id', true);
            $table->dateTime('date_time')->useCurrent();
            $table->string('type_invmov', 20);
            $table->string('reason_invmov', 30);
            $table->integer('company_idfk')->index('fk_invmov_company');
            $table->integer('origin_branch_idfk')->nullable()->index('fk_invmov_origin_branch');
            $table->integer('destination_branch_idfk')->nullable()->index('fk_invmov_destination_branch');
            $table->integer('userr_idfk')->nullable()->index('fk_invmov_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movement');
    }
};
