<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movement_item', function (Blueprint $table) {
            $table->integer('previous_stock')->nullable()->after('amount');
            $table->integer('new_stock')->nullable()->after('previous_stock');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movement_item', function (Blueprint $table) {
            $table->dropColumn(['previous_stock', 'new_stock']);
        });
    }
};