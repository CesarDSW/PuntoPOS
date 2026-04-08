<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tag_customer', function (Blueprint $table) {
            $table->integer('tag_customer_id', true);
            $table->integer('customer_idfk');
            $table->integer('tag_idfk');
            
            $table->foreign('customer_idfk', 'fk_tag_customer_customer')
            ->references('customer_id')
            ->on('customer')
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->foreign('tag_idfk', 'fk_tag_customer_tag')
            ->references('tag_id')
            ->on('tag')
            ->onDelete('cascade')
            ->onUpdate('cascade');
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('tag_customer');
    }
};
