<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        Schema::create('branch_product_stock', function (Blueprint $table) {
            $table->integer('branch_idfk');
            $table->integer('product_idfk')->index('fk_bps_product');
            $table->integer('stocks')->default(0);
            $table->integer('minimum_stock')->nullable();
            $table->boolean('status_stock')->default(true);

            $table->primary(['branch_idfk', 'product_idfk']);
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('cash_register_session', function (Blueprint $table) {
            $table->integer('cash_session_id', true);
            $table->integer('company_idfk')->index('fk_cash_session_company');
            $table->integer('branch_idfk')->index('fk_cash_session_branch');
            $table->integer('opened_by_userr_idfk')->index('fk_cash_session_open_user');
            $table->integer('closed_by_userr_idfk')->nullable()->index('fk_cash_session_close_user');
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->decimal('opening_amount', 19, 4)->default(0);
            $table->decimal('closing_amount', 19, 4)->nullable();
            $table->string('notes_opening')->nullable();
            $table->string('notes_closing')->nullable();
            $table->string('status_cash', 20)->default('ABIERTA');
        });

        Schema::create('category', function (Blueprint $table) {
            $table->integer('category_id', true);
            $table->string('name_category', 15);
            $table->string('description_category', 250)->nullable();
            $table->string('type_category', 8);
            $table->integer('company_idfk')->nullable()->index('fk_category_company');
            $table->boolean('status_category')->default(true);
        });

        Schema::create('company', function (Blueprint $table) {
            $table->integer('company_id', true);
            $table->string('name_company', 100)->unique();
            $table->string('rfc', 13)->nullable();
            $table->string('address', 150)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zip_code', 8)->nullable();
            $table->string('phone', 10)->nullable();
            $table->string('email', 320)->nullable();
            $table->string('currency', 20)->nullable();
            $table->string('logo')->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->string('description_company', 250)->nullable();
            $table->json('payment_methods')->nullable();
            $table->boolean('onboarding_completed')->default(false);
            $table->integer('owner_user_id')->nullable()->index('fk_company_owner');
        });

        Schema::create('company_setting', function (Blueprint $table) {
            $table->integer('company_setting_id', true);
            $table->integer('company_idfk')->unique();
            $table->boolean('notify_low_stock')->default(true);
            $table->boolean('notify_sale_cancelled')->default(true);
            $table->boolean('notify_sale_pending')->default(true);
            $table->boolean('notify_sale_completed')->default(true);
            $table->boolean('notify_out_of_stock')->default(true);
            $table->string('timezone', 100)->default('Ciudad de México (GMT-6)');
            $table->string('date_format', 30)->default('DD/MM/YYYY');
            $table->string('time_format', 20)->default('24 horas');
            $table->boolean('auto_print')->default(true);
            $table->boolean('show_taxes')->default(true);
            $table->string('printer_width', 10)->default('80mm');
            $table->string('price_decimals', 20)->default('2');
        });

        Schema::create('customer', function (Blueprint $table) {
            $table->integer('customer_id', true);
            $table->string('customer_code', 20)->nullable();
            $table->string('name_customer', 150);
            $table->string('phone', 10);
            $table->string('email', 320);
            $table->integer('company_idfk')->index('idx_customer_company_idfk');
            $table->boolean('status_customer')->default(true);
        });

        Schema::create('external_integrations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_idfk')->nullable()->index();
            $table->unsignedBigInteger('branch_idfk')->nullable()->index();
            $table->unsignedBigInteger('userr_idfk')->nullable()->index();
            $table->string('source_app', 50)->default('clientedigital')->index();
            $table->unsignedBigInteger('external_user_id')->nullable()->index();
            $table->string('external_base_url');
            $table->text('access_token');
            $table->string('status', 30)->default('active')->index();
            $table->timestamp('last_products_sync_at')->nullable();
            $table->timestamp('last_sales_sync_at')->nullable();
            $table->timestamps();
        });

        Schema::create('external_sync_maps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('external_integration_id')->index();
            $table->string('entity_type', 50)->index();
            $table->string('external_id', 100)->index();
            $table->string('local_table', 100)->index();
            $table->unsignedBigInteger('local_id')->index();
            $table->timestamps();

            $table->unique(['external_integration_id', 'entity_type', 'external_id'], 'external_sync_unique');
        });

        Schema::create('inventory_movement', function (Blueprint $table) {
            $table->integer('invmov_id', true);
            $table->dateTime('date_time')->useCurrent();
            $table->string('type_invmov', 20);
            $table->string('reason_invmov', 30);
            $table->integer('company_idfk')->index('fk_invmov_company');
            $table->integer('origin_branch_idfk')->nullable()->index('fk_invmov_origin_branch');
            $table->integer('destination_branch_idfk')->nullable()->index('fk_invmov_destination_branch');
            $table->integer('userr_idfk')->nullable()->index('fk_invmov_user');
        });

        Schema::create('inventory_movement_item', function (Blueprint $table) {
            $table->integer('invmov_idfk');
            $table->integer('product_idfk')->index('fk_invmov_item_product');
            $table->integer('amount');
            $table->integer('previous_stock')->nullable();
            $table->integer('new_stock')->nullable();

            $table->primary(['invmov_idfk', 'product_idfk']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->integer('payment_id', true);
            $table->dateTime('date_time');
            $table->string('payment_method', 70);
            $table->string('status_payment', 40);
            $table->decimal('commission', 19, 4);
            $table->decimal('amount_paid', 19, 4)->default(0);
            $table->decimal('change_given', 19, 4)->default(0);
            $table->string('reference_payment', 100)->nullable();
            $table->integer('sale_idfk')->index('fk_payment_sale');
            $table->integer('customer_idfk')->index('fk_payment_customer');
            $table->integer('attempts')->default(0);
        });

        Schema::create('permission', function (Blueprint $table) {
            $table->integer('permission_id', true);
            $table->string('code_permission', 80)->unique('ux_permission_code');
            $table->string('description_permission', 200)->nullable();
        });

        Schema::create('permission_grant', function (Blueprint $table) {
            $table->integer('granter_rol_idfk');
            $table->integer('permission_idfk')->index('fk_permgrant_perm');

            $table->primary(['granter_rol_idfk', 'permission_idfk']);
        });

        Schema::create('plann', function (Blueprint $table) {
            $table->integer('plan_id', true);
            $table->string('name_plan', 40);
            $table->decimal('price', 19, 4);
            $table->integer('subscription_idfk')->index('fk_plann_subscription');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->decimal('price', 10);
            $table->integer('stock')->default(0);
            $table->timestamps();
        });

        Schema::create('productt', function (Blueprint $table) {
            $table->integer('product_id', true);
            $table->string('name_product', 80);
            $table->string('code_product', 15);
            $table->string('description_product', 250)->nullable();
            $table->decimal('price', 19, 4);
            $table->decimal('cost', 19, 4)->nullable();
            $table->boolean('status_product')->nullable();
            $table->integer('company_idfk');
            $table->integer('category_idfk')->index('fk_product_category');

            $table->unique(['company_idfk', 'code_product'], 'ux_product_company_code');
        });

        Schema::create('rol', function (Blueprint $table) {
            $table->integer('rol_id', true);
            $table->string('type_rol', 30);
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->integer('rol_idfk');
            $table->integer('permission_idfk')->index('fk_roleperm_perm');
            $table->boolean('allow')->default(true);

            $table->primary(['rol_idfk', 'permission_idfk']);
        });

        Schema::create('sale', function (Blueprint $table) {
            $table->integer('sale_id', true);
            $table->dateTime('date_time')->useCurrent();
            $table->integer('company_idfk');
            $table->integer('branch_idfk');
            $table->integer('cashier_userr_idfk')->index('fk_sale_cashier');
            $table->integer('customer_idfk')->index('fk_sale_customer');
            $table->decimal('subtotal', 19, 4)->default(0);
            $table->decimal('discount', 19, 4)->default(0);
            $table->decimal('total', 19, 4)->default(0);
            $table->string('payment_method');
            $table->string('status_sale', 20)->default('PAGADA');

            $table->index(['branch_idfk', 'date_time'], 'ix_sale_branch_date');
            $table->index(['company_idfk', 'date_time'], 'ix_sale_company_date');
        });

        Schema::create('saleitem', function (Blueprint $table) {
            $table->integer('saleitem_id', true);
            $table->integer('sale_idfk')->index('ix_saleitem_sale');
            $table->string('item_type', 10);
            $table->integer('product_idfk')->nullable()->index('fk_saleitem_product');
            $table->integer('service_idfk')->nullable()->index('fk_saleitem_service');
            $table->integer('amount');
            $table->decimal('unit_price', 19, 4);
            $table->decimal('discount', 19, 4)->default(0);
            $table->decimal('total_line', 19, 4)->default(0);
        });

        Schema::create('servicee', function (Blueprint $table) {
            $table->integer('service_id', true);
            $table->string('name_service', 80);
            $table->string('code_service', 15)->nullable();
            $table->string('description_service', 250)->nullable();
            $table->decimal('price', 19, 4);
            $table->boolean('status_service')->default(true);
            $table->integer('company_idfk')->index('fk_service_company');
            $table->integer('category_idfk')->nullable()->index('fk_service_category');

            $table->unique(['company_idfk', 'code_service'], 'ux_service_company_code');
        });

        Schema::create('subscription', function (Blueprint $table) {
            $table->integer('subscription_id', true);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('status_subscription');
            $table->string('status')->nullable()->index();
            $table->string('plan')->nullable();
            $table->integer('company_idfk')->index('fk_subscription_company');
            $table->integer('user_idfk')->nullable()->index();
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('stripe_subscription_id')->nullable()->index();
        });

        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('support_ticket_id')->index('support_ticket_messages_support_ticket_id_foreign');
            $table->unsignedBigInteger('sender_id');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('pendiente');
            $table->timestamps();
        });

        Schema::create('system_notification', function (Blueprint $table) {
            $table->integer('notification_id', true);
            $table->integer('company_idfk')->index('system_notification_company_idfk_foreign');
            $table->integer('branch_idfk')->nullable()->index('system_notification_branch_idfk_foreign');
            $table->unsignedBigInteger('target_user_idfk')->nullable();
            $table->string('type_code', 40);
            $table->string('title', 120);
            $table->text('message');
            $table->string('reference_type', 40)->nullable();
            $table->integer('reference_id')->nullable();
            $table->string('action_url')->nullable();
            $table->string('dedupe_key', 120)->nullable()->unique();
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('read_at')->nullable();
        });

        Schema::create('user_branch', function (Blueprint $table) {
            $table->integer('userr_idfk');
            $table->integer('branch_idfk')->index('fk_user_branch_branch');

            $table->primary(['userr_idfk', 'branch_idfk']);
        });

        Schema::create('user_permission', function (Blueprint $table) {
            $table->integer('userr_idfk');
            $table->integer('permission_idfk')->index('fk_userperm_perm');
            $table->boolean('allow')->default(true);

            $table->primary(['userr_idfk', 'permission_idfk']);
        });

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->bigIncrements('user_preference_id');
            $table->unsignedBigInteger('userr_idfk')->unique();
            $table->string('theme')->default('light');
            $table->timestamps();
        });

        Schema::create('userr', function (Blueprint $table) {
            $table->integer('userr_id', true);
            $table->string('name_user', 100);
            $table->string('phone', 10);
            $table->string('email', 320)->unique('ux_userr_email');
            $table->string('google_id', 100)->nullable();
            $table->string('google_email', 320)->nullable();
            $table->string('name_company', 100);
            $table->string('password');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->text('two_factor_confirmed_at')->nullable();
            $table->integer('rol_idfk')->index('fk_userr_rol');
            $table->integer('company_idfk')->nullable()->index('fk_userr_company');
            $table->boolean('state')->default(true);
            $table->rememberToken();
        });

        Schema::create('work_shift', function (Blueprint $table) {
            $table->integer('shift_id', true);
            $table->integer('cash_session_idfk')->index('fk_shift_cash_session');
            $table->integer('company_idfk')->index('fk_shift_company');
            $table->integer('branch_idfk')->index('fk_shift_branch');
            $table->integer('userr_idfk')->index('fk_shift_user');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->string('status_shift', 20)->default('ABIERTO');
            $table->string('shift_type', 30)->default('CAJERO');
            $table->string('notes_shift')->nullable();
        });

        Schema::table('branch', function (Blueprint $table) {
            $table->foreign(['company_idfk'], 'FK_branch_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('branch_product_stock', function (Blueprint $table) {
            $table->foreign(['branch_idfk'], 'FK_bps_branch')->references(['branch_id'])->on('branch')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['product_idfk'], 'FK_bps_product')->references(['product_id'])->on('productt')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('cash_register_session', function (Blueprint $table) {
            $table->foreign(['branch_idfk'], 'fk_cash_session_branch')->references(['branch_id'])->on('branch')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['closed_by_userr_idfk'], 'fk_cash_session_close_user')->references(['userr_id'])->on('userr')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['company_idfk'], 'fk_cash_session_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['opened_by_userr_idfk'], 'fk_cash_session_open_user')->references(['userr_id'])->on('userr')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('category', function (Blueprint $table) {
            $table->foreign(['company_idfk'], 'FK_category_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('company', function (Blueprint $table) {
            $table->foreign(['owner_user_id'], 'FK_company_owner')->references(['userr_id'])->on('userr')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('company_setting', function (Blueprint $table) {
            $table->foreign(['company_idfk'])->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('cascade');
        });

        Schema::table('customer', function (Blueprint $table) {
            $table->foreign(['company_idfk'], 'fk_customer_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('inventory_movement', function (Blueprint $table) {
            $table->foreign(['company_idfk'], 'FK_invmov_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['destination_branch_idfk'], 'FK_invmov_destination_branch')->references(['branch_id'])->on('branch')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['origin_branch_idfk'], 'FK_invmov_origin_branch')->references(['branch_id'])->on('branch')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['userr_idfk'], 'FK_invmov_user')->references(['userr_id'])->on('userr')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('inventory_movement_item', function (Blueprint $table) {
            $table->foreign(['invmov_idfk'], 'FK_invmov_item_mov')->references(['invmov_id'])->on('inventory_movement')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['product_idfk'], 'FK_invmov_item_product')->references(['product_id'])->on('productt')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign(['customer_idfk'], 'FK_payment_customer')->references(['customer_id'])->on('customer')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['sale_idfk'], 'FK_payment_sale')->references(['sale_id'])->on('sale')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('permission_grant', function (Blueprint $table) {
            $table->foreign(['permission_idfk'], 'FK_permgrant_perm')->references(['permission_id'])->on('permission')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['granter_rol_idfk'], 'FK_permgrant_role')->references(['rol_id'])->on('rol')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('plann', function (Blueprint $table) {
            $table->foreign(['subscription_idfk'], 'FK_plann_subscription')->references(['subscription_id'])->on('subscription')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('productt', function (Blueprint $table) {
            $table->foreign(['category_idfk'], 'FK_product_category')->references(['category_id'])->on('category')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['company_idfk'], 'FK_product_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('role_permission', function (Blueprint $table) {
            $table->foreign(['permission_idfk'], 'FK_roleperm_perm')->references(['permission_id'])->on('permission')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['rol_idfk'], 'FK_roleperm_role')->references(['rol_id'])->on('rol')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('sale', function (Blueprint $table) {
            $table->foreign(['branch_idfk'], 'FK_sale_branch')->references(['branch_id'])->on('branch')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['cashier_userr_idfk'], 'FK_sale_cashier')->references(['userr_id'])->on('userr')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['company_idfk'], 'FK_sale_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['customer_idfk'], 'FK_sale_customer')->references(['customer_id'])->on('customer')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('saleitem', function (Blueprint $table) {
            $table->foreign(['product_idfk'], 'FK_saleitem_product')->references(['product_id'])->on('productt')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['sale_idfk'], 'FK_saleitem_sale')->references(['sale_id'])->on('sale')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['service_idfk'], 'FK_saleitem_service')->references(['service_id'])->on('servicee')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('servicee', function (Blueprint $table) {
            $table->foreign(['category_idfk'], 'FK_service_category')->references(['category_id'])->on('category')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['company_idfk'], 'FK_service_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('subscription', function (Blueprint $table) {
            $table->foreign(['company_idfk'], 'FK_subscription_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('support_ticket_messages', function (Blueprint $table) {
            $table->foreign(['support_ticket_id'])->references(['id'])->on('support_tickets')->onUpdate('restrict')->onDelete('cascade');
        });

        Schema::table('system_notification', function (Blueprint $table) {
            $table->foreign(['branch_idfk'])->references(['branch_id'])->on('branch')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['company_idfk'])->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('cascade');
        });

        Schema::table('user_branch', function (Blueprint $table) {
            $table->foreign(['branch_idfk'], 'FK_user_branch_branch')->references(['branch_id'])->on('branch')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['userr_idfk'], 'FK_user_branch_user')->references(['userr_id'])->on('userr')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('user_permission', function (Blueprint $table) {
            $table->foreign(['permission_idfk'], 'FK_userperm_perm')->references(['permission_id'])->on('permission')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['userr_idfk'], 'FK_userperm_user')->references(['userr_id'])->on('userr')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('userr', function (Blueprint $table) {
            $table->foreign(['company_idfk'], 'FK_userr_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['rol_idfk'], 'FK_userr_rol')->references(['rol_id'])->on('rol')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('work_shift', function (Blueprint $table) {
            $table->foreign(['branch_idfk'], 'fk_shift_branch')->references(['branch_id'])->on('branch')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['cash_session_idfk'], 'fk_shift_cash_session')->references(['cash_session_id'])->on('cash_register_session')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['company_idfk'], 'fk_shift_company')->references(['company_id'])->on('company')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['userr_idfk'], 'fk_shift_user')->references(['userr_id'])->on('userr')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_shift', function (Blueprint $table) {
            $table->dropForeign('fk_shift_branch');
            $table->dropForeign('fk_shift_cash_session');
            $table->dropForeign('fk_shift_company');
            $table->dropForeign('fk_shift_user');
        });

        Schema::table('userr', function (Blueprint $table) {
            $table->dropForeign('FK_userr_company');
            $table->dropForeign('FK_userr_rol');
        });

        Schema::table('user_permission', function (Blueprint $table) {
            $table->dropForeign('FK_userperm_perm');
            $table->dropForeign('FK_userperm_user');
        });

        Schema::table('user_branch', function (Blueprint $table) {
            $table->dropForeign('FK_user_branch_branch');
            $table->dropForeign('FK_user_branch_user');
        });

        Schema::table('system_notification', function (Blueprint $table) {
            $table->dropForeign('system_notification_branch_idfk_foreign');
            $table->dropForeign('system_notification_company_idfk_foreign');
        });

        Schema::table('support_ticket_messages', function (Blueprint $table) {
            $table->dropForeign('support_ticket_messages_support_ticket_id_foreign');
        });

        Schema::table('subscription', function (Blueprint $table) {
            $table->dropForeign('FK_subscription_company');
        });

        Schema::table('servicee', function (Blueprint $table) {
            $table->dropForeign('FK_service_category');
            $table->dropForeign('FK_service_company');
        });

        Schema::table('saleitem', function (Blueprint $table) {
            $table->dropForeign('FK_saleitem_product');
            $table->dropForeign('FK_saleitem_sale');
            $table->dropForeign('FK_saleitem_service');
        });

        Schema::table('sale', function (Blueprint $table) {
            $table->dropForeign('FK_sale_branch');
            $table->dropForeign('FK_sale_cashier');
            $table->dropForeign('FK_sale_company');
            $table->dropForeign('FK_sale_customer');
        });

        Schema::table('role_permission', function (Blueprint $table) {
            $table->dropForeign('FK_roleperm_perm');
            $table->dropForeign('FK_roleperm_role');
        });

        Schema::table('productt', function (Blueprint $table) {
            $table->dropForeign('FK_product_category');
            $table->dropForeign('FK_product_company');
        });

        Schema::table('plann', function (Blueprint $table) {
            $table->dropForeign('FK_plann_subscription');
        });

        Schema::table('permission_grant', function (Blueprint $table) {
            $table->dropForeign('FK_permgrant_perm');
            $table->dropForeign('FK_permgrant_role');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('FK_payment_customer');
            $table->dropForeign('FK_payment_sale');
        });

        Schema::table('inventory_movement_item', function (Blueprint $table) {
            $table->dropForeign('FK_invmov_item_mov');
            $table->dropForeign('FK_invmov_item_product');
        });

        Schema::table('inventory_movement', function (Blueprint $table) {
            $table->dropForeign('FK_invmov_company');
            $table->dropForeign('FK_invmov_destination_branch');
            $table->dropForeign('FK_invmov_origin_branch');
            $table->dropForeign('FK_invmov_user');
        });

        Schema::table('customer', function (Blueprint $table) {
            $table->dropForeign('fk_customer_company');
        });

        Schema::table('company_setting', function (Blueprint $table) {
            $table->dropForeign('company_setting_company_idfk_foreign');
        });

        Schema::table('company', function (Blueprint $table) {
            $table->dropForeign('FK_company_owner');
        });

        Schema::table('category', function (Blueprint $table) {
            $table->dropForeign('FK_category_company');
        });

        Schema::table('cash_register_session', function (Blueprint $table) {
            $table->dropForeign('fk_cash_session_branch');
            $table->dropForeign('fk_cash_session_close_user');
            $table->dropForeign('fk_cash_session_company');
            $table->dropForeign('fk_cash_session_open_user');
        });

        Schema::table('branch_product_stock', function (Blueprint $table) {
            $table->dropForeign('FK_bps_branch');
            $table->dropForeign('FK_bps_product');
        });

        Schema::table('branch', function (Blueprint $table) {
            $table->dropForeign('FK_branch_company');
        });

        Schema::dropIfExists('work_shift');

        Schema::dropIfExists('userr');

        Schema::dropIfExists('user_preferences');

        Schema::dropIfExists('user_permission');

        Schema::dropIfExists('user_branch');

        Schema::dropIfExists('system_notification');

        Schema::dropIfExists('support_tickets');

        Schema::dropIfExists('support_ticket_messages');

        Schema::dropIfExists('subscription');

        Schema::dropIfExists('servicee');

        Schema::dropIfExists('saleitem');

        Schema::dropIfExists('sale');

        Schema::dropIfExists('role_permission');

        Schema::dropIfExists('rol');

        Schema::dropIfExists('productt');

        Schema::dropIfExists('products');

        Schema::dropIfExists('plann');

        Schema::dropIfExists('permission_grant');

        Schema::dropIfExists('permission');

        Schema::dropIfExists('payments');

        Schema::dropIfExists('password_reset_tokens');

        Schema::dropIfExists('inventory_movement_item');

        Schema::dropIfExists('inventory_movement');

        Schema::dropIfExists('external_sync_maps');

        Schema::dropIfExists('external_integrations');

        Schema::dropIfExists('customer');

        Schema::dropIfExists('company_setting');

        Schema::dropIfExists('company');

        Schema::dropIfExists('category');

        Schema::dropIfExists('cash_register_session');

        Schema::dropIfExists('branches');

        Schema::dropIfExists('branch_product_stock');

        Schema::dropIfExists('branch');
    }
};
