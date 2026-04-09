<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_register_session', function (Blueprint $table) {
            $table->integer('cash_session_id', true);

            $table->integer('company_idfk');
            $table->integer('branch_idfk');

            $table->integer('opened_by_userr_idfk');
            $table->integer('closed_by_userr_idfk')->nullable();

            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();

            $table->decimal('opening_amount', 19, 4)->default(0);
            $table->decimal('closing_amount', 19, 4)->nullable();

            $table->string('notes_opening', 255)->nullable();
            $table->string('notes_closing', 255)->nullable();

            $table->string('status_cash', 20)->default('ABIERTA');

            $table->index('company_idfk', 'fk_cash_session_company');
            $table->index('branch_idfk', 'fk_cash_session_branch');
            $table->index('opened_by_userr_idfk', 'fk_cash_session_open_user');
            $table->index('closed_by_userr_idfk', 'fk_cash_session_close_user');

            $table->foreign('company_idfk', 'fk_cash_session_company')
                ->references('company_id')
                ->on('company');

            $table->foreign('branch_idfk', 'fk_cash_session_branch')
                ->references('branch_id')
                ->on('branch');

            $table->foreign('opened_by_userr_idfk', 'fk_cash_session_open_user')
                ->references('userr_id')
                ->on('userr');

            $table->foreign('closed_by_userr_idfk', 'fk_cash_session_close_user')
                ->references('userr_id')
                ->on('userr');
        });
    }

    public function down(): void
    {
        Schema::table('cash_register_session', function (Blueprint $table) {
            $table->dropForeign('fk_cash_session_company');
            $table->dropForeign('fk_cash_session_branch');
            $table->dropForeign('fk_cash_session_open_user');
            $table->dropForeign('fk_cash_session_close_user');
        });

        Schema::dropIfExists('cash_register_session');
    }
};