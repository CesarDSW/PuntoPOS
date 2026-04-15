<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_shift', function (Blueprint $table) {
            $table->integer('shift_id', true);

            $table->integer('cash_session_idfk');
            $table->integer('company_idfk');
            $table->integer('branch_idfk');
            $table->integer('userr_idfk');

            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();

            $table->string('status_shift', 20)->default('ABIERTO');
            $table->string('shift_type', 30)->default('CAJERO');
            $table->string('notes_shift', 255)->nullable();

            $table->index('cash_session_idfk', 'fk_shift_cash_session');
            $table->index('company_idfk', 'fk_shift_company');
            $table->index('branch_idfk', 'fk_shift_branch');
            $table->index('userr_idfk', 'fk_shift_user');

            $table->foreign('cash_session_idfk', 'fk_shift_cash_session')
                ->references('cash_session_id')
                ->on('cash_register_session');

            $table->foreign('company_idfk', 'fk_shift_company')
                ->references('company_id')
                ->on('company');

            $table->foreign('branch_idfk', 'fk_shift_branch')
                ->references('branch_id')
                ->on('branch');

            $table->foreign('userr_idfk', 'fk_shift_user')
                ->references('userr_id')
                ->on('userr');
        });
    }

    public function down(): void
    {
        Schema::table('work_shift', function (Blueprint $table) {
            $table->dropForeign('fk_shift_cash_session');
            $table->dropForeign('fk_shift_company');
            $table->dropForeign('fk_shift_branch');
            $table->dropForeign('fk_shift_user');
        });

        Schema::dropIfExists('work_shift');
    }
};