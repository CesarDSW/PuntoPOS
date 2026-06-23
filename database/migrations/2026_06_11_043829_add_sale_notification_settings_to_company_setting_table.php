<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_setting', function (Blueprint $table) {
            if (!Schema::hasColumn('company_setting', 'notify_sale_pending')) {
                $table->boolean('notify_sale_pending')->default(true)->after('notify_sale_cancelled');
            }

            if (!Schema::hasColumn('company_setting', 'notify_sale_completed')) {
                $table->boolean('notify_sale_completed')->default(true)->after('notify_sale_pending');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_setting', function (Blueprint $table) {
            if (Schema::hasColumn('company_setting', 'notify_sale_completed')) {
                $table->dropColumn('notify_sale_completed');
            }

            if (Schema::hasColumn('company_setting', 'notify_sale_pending')) {
                $table->dropColumn('notify_sale_pending');
            }
        });
    }
};