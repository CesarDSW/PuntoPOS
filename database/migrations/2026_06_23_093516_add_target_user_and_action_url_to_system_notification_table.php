<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_notification', function (Blueprint $table) {
            if (!Schema::hasColumn('system_notification', 'target_user_idfk')) {
                $table->unsignedBigInteger('target_user_idfk')->nullable()->after('branch_idfk');
            }

            if (!Schema::hasColumn('system_notification', 'action_url')) {
                $table->string('action_url')->nullable()->after('reference_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_notification', function (Blueprint $table) {
            if (Schema::hasColumn('system_notification', 'action_url')) {
                $table->dropColumn('action_url');
            }

            if (Schema::hasColumn('system_notification', 'target_user_idfk')) {
                $table->dropColumn('target_user_idfk');
            }
        });
    }
};