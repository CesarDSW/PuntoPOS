<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tag', function (Blueprint $table) {
            $table->foreign(['customer_idfk'], 'FK_tag_customer')->references(['customer_id'])->on('customer')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tag', function (Blueprint $table) {
            $table->dropForeign('FK_tag_customer');
        });
    }
};
