<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('company_setting')) {
            Schema::table('company_setting', function (Blueprint $table) {
                if (Schema::hasColumn('company_setting', 'theme')) {
                    $table->dropColumn('theme');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('company_setting')) {
            Schema::table('company_setting', function (Blueprint $table) {
                if (!Schema::hasColumn('company_setting', 'theme')) {
                    $table->string('theme')->default('light')->after('printer_width');
                }
            });
        }
    }
};