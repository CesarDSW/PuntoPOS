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
        Schema::create('branch', function (Blueprint $table) {
            $table->integer('branch_id', true);
            $table->string('name_branch', 50);
            $table->string('address', 50);
            $table->string('city', 50);
            $table->string('state', 50);
            $table->string('phone', 10)->nullable();
            $table->string('responsible', 50)->nullable();
            $table->string('email', 320)->nullable();
            $table->integer('company_idfk')->index('fk_branch_company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch');
    }
};
