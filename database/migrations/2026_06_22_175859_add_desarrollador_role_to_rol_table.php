<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('rol')
            ->where('type_rol', 'DEV')
            ->exists();

        if (!$exists) {
            DB::table('rol')->insert([
                'type_rol' => 'DEV'
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('rol', function (Blueprint $table) {
            DB::table('rol')
                ->where('type_rol', 'DEV')
                ->delete();
        });
    }
};
