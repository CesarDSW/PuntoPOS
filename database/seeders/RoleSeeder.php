<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void 
    {
        $roles = [
            'ADMINISTRADOR',
            'GERENTE',
            'CAJERO',
            'DEV',
        ];

        foreach ($roles as $role) {
            DB::table('rol')->updateOrInsert(
                ['type_rol' => $role],
                ['type_rol' => $role]
            );
        }
    }
}