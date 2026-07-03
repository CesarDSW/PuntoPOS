<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void 
    {
        $roles = [
            1 => 'ADMINISTRADOR',
            2 => 'GERENTE',
            3 => 'CAJERO',
            4 => 'DEV',
        ];

        foreach ($roles as $id => $role) {
            DB::table('rol')->updateOrInsert(
                ['id' => $id],
                ['type_rol' => $role]
            );
        }
    }
}