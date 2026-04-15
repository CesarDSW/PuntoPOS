<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount_paid', 19, 4)
                ->default(0)
                ->after('commission');

            $table->decimal('change_given', 19, 4)
                ->default(0)
                ->after('amount_paid');

            $table->string('reference_payment', 100)
                ->nullable()
                ->after('change_given');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'amount_paid',
                'change_given',
                'reference_payment',
            ]);
        });
    }
};