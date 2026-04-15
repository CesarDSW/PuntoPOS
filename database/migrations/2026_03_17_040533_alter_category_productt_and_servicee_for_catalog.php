<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category', function (Blueprint $table) {
            $table->string('description_category', 250)
                ->nullable()
                ->after('name_category');

            $table->unsignedBigInteger('company_idfk')
                ->nullable()
                ->after('type_category');

            $table->boolean('status_category')
                ->default(1)
                ->after('company_idfk');

            $table->foreign('company_idfk', 'FK_category_company')
                ->references('company_id')
                ->on('company');
        });

        Schema::table('productt', function (Blueprint $table) {
            $table->decimal('cost', 19, 4)
                ->nullable()
                ->after('price');
        });

        Schema::table('servicee', function (Blueprint $table) {
            $table->string('code_service', 15)
                ->nullable()
                ->after('name_service');

            $table->unique(['company_idfk', 'code_service'], 'UX_service_company_code');
        });
    }

    public function down(): void
    {
        Schema::table('servicee', function (Blueprint $table) {
            $table->dropUnique('UX_service_company_code');
            $table->dropColumn('code_service');
        });

        Schema::table('productt', function (Blueprint $table) {
            $table->dropColumn('cost');
        });

        Schema::table('category', function (Blueprint $table) {
            $table->dropForeign('FK_category_company');
            $table->dropColumn([
                'description_category',
                'company_idfk',
                'status_category',
            ]);
        });
    }
};