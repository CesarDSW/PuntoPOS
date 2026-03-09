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
        Schema::create('userr', function (Blueprint $table) {
            $table->integer('userr_id', true);
            $table->string('name_user', 100);
            $table->string('phone', 10);
            $table->string('email', 320)->unique('ux_userr_email');
            $table->string('name_company', 100);
            $table->string('password', 255);
            $table->integer('rol_idfk')->index('fk_userr_rol');
            $table->integer('company_idfk')->nullable()->index('fk_userr_company');
            $table->boolean('state')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('userr');
    }
};
