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
        Schema::table('plann', function (Blueprint $table) {
            $table->foreign(['subscription_idfk'], 'FK_plann_subscription')->references(['subscription_id'])->on('subscription')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plann', function (Blueprint $table) {
            $table->dropForeign('FK_plann_subscription');
        });
    }
};
