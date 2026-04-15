<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company', function (Blueprint $table) {
            $table->integer('company_id', true);
            $table->string('name_company', 100)->unique();
            $table->string('rfc', 13)->nullable();
            $table->string('address', 150)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zip_code', 8)->nullable();
            $table->string('phone', 10)->nullable();
            $table->string('email', 320)->nullable();
            $table->string('currency', 20)->nullable();
            $table->string('logo', 255)->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->string('description_company', 250)->nullable();
            $table->json('payment_methods')->nullable();
            $table->boolean('onboarding_completed')->default(false);
            $table->integer('owner_user_id')->nullable();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('company');
    }
};
