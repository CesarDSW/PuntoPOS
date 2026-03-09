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
        Schema::create('plann', function (Blueprint $table) {
            $table->integer('plan_id', true);
            $table->string('name_plan', 40);
            $table->decimal('price', 19, 4);
            $table->integer('subscription_idfk')->index('fk_plann_subscription');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plann');
    }
};
