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
        Schema::create('company', function (Blueprint $table) {
            $table->integer('company_id', true);
            $table->string('rfc', 13);
            $table->string('address', 50);
            $table->string('city', 50);
            $table->string('state', 50);
            $table->string('zip_code', 8);
            $table->string('phone', 10);
            $table->string('email', 320);
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->string('description_company', 250)->nullable();
            $table->integer('owner_user_id')->index('fk_company_owner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company');
    }
};
