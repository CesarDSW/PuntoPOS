<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('userr', 'remember_token')) {
            Schema::table('userr', function (Blueprint $table) {
                $table->string('remember_token', 100)->nullable();
            });
        }
    }

    public function down(): void
    {
        if(Schema::hasColumn('userr', 'remember_token')) {
            Schema::table('userr', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        }
    }
};
