<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription', 'user_idfk')) {
                $table->integer('user_idfk')->nullable()->after('company_idfk')->index();
            }

            if (!Schema::hasColumn('subscription', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->after('user_idfk')->index();
            }

            if (!Schema::hasColumn('subscription', 'stripe_subscription_id')) {
                $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id')->index();
            }

            if (!Schema::hasColumn('subscription', 'status')) {
                $table->string('status')->nullable()->after('status_subscription')->index();
            }

            if (!Schema::hasColumn('subscription', 'plan')) {
                $table->string('plan')->nullable()->after('status');
            }
        });

        DB::statement("
            UPDATE subscription
            SET status = CASE
                WHEN status_subscription = 1 THEN 'activa'
                ELSE 'vencida'
            END
            WHERE status IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('subscription', function (Blueprint $table) {
            if (Schema::hasColumn('subscription', 'plan')) {
                $table->dropColumn('plan');
            }

            if (Schema::hasColumn('subscription', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('subscription', 'stripe_subscription_id')) {
                $table->dropColumn('stripe_subscription_id');
            }

            if (Schema::hasColumn('subscription', 'stripe_customer_id')) {
                $table->dropColumn('stripe_customer_id');
            }

            if (Schema::hasColumn('subscription', 'user_idfk')) {
                $table->dropColumn('user_idfk');
            }
        });
    }
};