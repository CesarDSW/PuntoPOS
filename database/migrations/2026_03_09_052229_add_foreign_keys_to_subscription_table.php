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
        Schema::table('subscription', function (Blueprint $table) {
            $table->foreign(['company_idfk'], 'FK_subscription_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription', function (Blueprint $table) {
            $table->dropForeign('FK_subscription_company');
        });
    }
};
