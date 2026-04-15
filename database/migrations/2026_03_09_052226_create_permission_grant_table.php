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
        Schema::create('permission_grant', function (Blueprint $table) {
            $table->integer('granter_rol_idfk');
            $table->integer('permission_idfk')->index('fk_permgrant_perm');

            $table->primary(['granter_rol_idfk', 'permission_idfk']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_grant');
    }
};
