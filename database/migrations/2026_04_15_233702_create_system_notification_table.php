<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_notification', function (Blueprint $table) {
            $table->integer('notification_id', true);
            $table->integer('company_idfk');
            $table->integer('branch_idfk')->nullable();

            $table->string('type_code', 40);
            $table->string('title', 120);
            $table->text('message');

            $table->string('reference_type', 40)->nullable();
            $table->integer('reference_id')->nullable();

            $table->string('dedupe_key', 120)->nullable()->unique();
            $table->boolean('is_read')->default(false);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('read_at')->nullable();

            $table->foreign('company_idfk')->references('company_id')->on('company')->onDelete('cascade');
            $table->foreign('branch_idfk')->references('branch_id')->on('branch')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_notification');
    }
};
