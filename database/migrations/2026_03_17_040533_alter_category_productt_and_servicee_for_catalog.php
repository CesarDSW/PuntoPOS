<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CATEGORY
        if (!Schema::hasColumn('category', 'description_category')) {
            Schema::table('category', function (Blueprint $table) {
                $table->string('description_category', 250)
                    ->nullable()
                    ->after('name_category');
            });
        }

        if (!Schema::hasColumn('category', 'company_idfk')) {
            Schema::table('category', function (Blueprint $table) {
                // Ojo: usamos integer porque tu BD viene manejando ids tipo integer
                $table->integer('company_idfk')
                    ->nullable()
                    ->after('type_category');
            });
        }

        if (!Schema::hasColumn('category', 'status_category')) {
            Schema::table('category', function (Blueprint $table) {
                $table->boolean('status_category')
                    ->default(1)
                    ->after('company_idfk');
            });
        }

        if (
            Schema::hasColumn('category', 'company_idfk') &&
            !$this->foreignKeyExists('category', 'FK_category_company')
        ) {
            Schema::table('category', function (Blueprint $table) {
                $table->foreign('company_idfk', 'FK_category_company')
                    ->references('company_id')
                    ->on('company');
            });
        }

        // PRODUCTT
        if (!Schema::hasColumn('productt', 'cost')) {
            Schema::table('productt', function (Blueprint $table) {
                $table->decimal('cost', 19, 4)
                    ->nullable()
                    ->after('price');
            });
        }

        // SERVICEE
        if (!Schema::hasColumn('servicee', 'code_service')) {
            Schema::table('servicee', function (Blueprint $table) {
                $table->string('code_service', 15)
                    ->nullable()
                    ->after('name_service');
            });
        }

        if (
            Schema::hasColumn('servicee', 'company_idfk') &&
            Schema::hasColumn('servicee', 'code_service') &&
            !$this->indexExists('servicee', 'UX_service_company_code')
        ) {
            Schema::table('servicee', function (Blueprint $table) {
                $table->unique(['company_idfk', 'code_service'], 'UX_service_company_code');
            });
        }
    }

    public function down(): void
    {
        // SERVICEE
        if ($this->indexExists('servicee', 'UX_service_company_code')) {
            Schema::table('servicee', function (Blueprint $table) {
                $table->dropUnique('UX_service_company_code');
            });
        }

        if (Schema::hasColumn('servicee', 'code_service')) {
            Schema::table('servicee', function (Blueprint $table) {
                $table->dropColumn('code_service');
            });
        }

        // PRODUCTT
        if (Schema::hasColumn('productt', 'cost')) {
            Schema::table('productt', function (Blueprint $table) {
                $table->dropColumn('cost');
            });
        }

        // CATEGORY
        if ($this->foreignKeyExists('category', 'FK_category_company')) {
            Schema::table('category', function (Blueprint $table) {
                $table->dropForeign('FK_category_company');
            });
        }

        $categoryColumnsToDrop = [];

        if (Schema::hasColumn('category', 'description_category')) {
            $categoryColumnsToDrop[] = 'description_category';
        }

        if (Schema::hasColumn('category', 'company_idfk')) {
            $categoryColumnsToDrop[] = 'company_idfk';
        }

        if (Schema::hasColumn('category', 'status_category')) {
            $categoryColumnsToDrop[] = 'status_category';
        }

        if (!empty($categoryColumnsToDrop)) {
            Schema::table('category', function (Blueprint $table) use ($categoryColumnsToDrop) {
                $table->dropColumn($categoryColumnsToDrop);
            });
        }
    }

    private function foreignKeyExists(string $tableName, string $constraintName): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }
};