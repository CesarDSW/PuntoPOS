<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DeveloperUserSeeder extends Seeder
{
    public function run(): void
    {
        $developerRoleId = DB::table('rol')
            ->where('type_rol', 'DEV')
            ->value('rol_id');

        if (!$developerRoleId) {
            $developerRoleId = DB::table('rol')->insertGetId([
                'type_rol' => 'DEV',
            ]);
        }

        $email = 'dev@punto.com';

        $exists = DB::table('userr')
            ->where('email', $email)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('userr')->insert([
            'name_user' => 'Desarrollador Punto',
            'phone' => '0000000000',
            'email' => $email,
            'google_id' => null,
            'google_email' => null,
            'name_company' => 'Punto Soporte',
            'password' => Hash::make('Dev123456'),
            'rol_idfk' => $developerRoleId,
            'company_idfk' => null,
            'state' => 1,
        ]);
    }
}
