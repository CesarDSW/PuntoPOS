<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        Schema::table('userr', function (Blueprint $table) {
            $table->string('google_id', 100)->nullable()->after('email');
            $table->string('google_email', 320)->nullable()->after('google_id');
        });
    }

    public function down(): void
    {
        Schema::table('userr', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'google_email']);
        });
    }
};
