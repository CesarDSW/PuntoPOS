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
        if(Schema::hasColumn('company_setting', 'language')){
            Schema::table('company_setting', function (Blueprint $table) {
                $table->dropColumn('language');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if(!Schema::hasColumn('company_settings', 'language')) {
            Schema::table('company_setting', function (Blueprint $table) {
                $table->string('language', 50)
                    ->default('Español (México)')
                    ->after('notify_out_of_stock');
            });
        }
    }
};
