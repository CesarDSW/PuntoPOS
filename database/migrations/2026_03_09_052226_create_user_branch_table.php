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
        Schema::create('user_branch', function (Blueprint $table) {
            $table->integer('userr_idfk');
            $table->integer('branch_idfk')->index('fk_user_branch_branch');

            $table->primary(['userr_idfk', 'branch_idfk']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_branch');
    }
};
