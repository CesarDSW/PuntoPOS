<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            // Configuración / empresa
            'settings.profile.edit' => 'Editar perfil del negocio',
            'branch.switch' => 'Cambiar sucursal actual',
            'branch.create' => 'Crear sucursales',

            // Usuarios y permisos
            'users.create' => 'Crear usuarios',
            'users.create_admin' => 'Crear administradores',
            'users.create_manager' => 'Crear gerentes',
            'users.create_cashier' => 'Crear cajeros',
            'users.edit' => 'Editar usuarios',
            'users.edit_admin' => 'Editar administradores',
            'users.delete' => 'Eliminar usuarios',
            'users.delete_admin' => 'Eliminar administradores',
            'users.assign_branch' => 'Asignar sucursal a usuarios',
            'permissions.manage' => 'Administrar permisos por usuario',

            // Dashboard
            'dashboard.view' => 'Ver dashboard',

            // Catálogo
            'catalog.view' => 'Ver catálogo',
            'catalog.products.create' => 'Crear productos',
            'catalog.products.edit' => 'Editar productos',
            'catalog.products.delete' => 'Eliminar productos',
            'catalog.services.create' => 'Crear servicios',
            'catalog.services.edit' => 'Editar servicios',
            'catalog.services.delete' => 'Eliminar servicios',
            'catalog.categories.create' => 'Crear categorías',
            'catalog.categories.edit' => 'Editar categorías',
            'catalog.categories.delete' => 'Eliminar categorías',
            'catalog.mass_import' => 'Realizar carga masiva de catálogo',

            // Inventario
            'inventory.view' => 'Ver inventario',
            'inventory.adjust' => 'Realizar ajustes de inventario',
            'inventory.history.view' => 'Ver historial de inventario',

            // Ventas / POS
            'sales.view' => 'Ver ventas',
            'sales.create' => 'Crear ventas',
            'sales.cancel' => 'Cancelar ventas',
            'sales.pos.use' => 'Usar POS',
            'sales.ticket.print' => 'Imprimir tickets',
            'cash.open' => 'Abrir caja',
            'cash.close' => 'Cerrar caja',
            'cash.history.view' => 'Ver historial de cajas',

            // Reportes
            'reports.view' => 'Ver reportes',
            'reports.export' => 'Exportar reportes',
            'reports.profit.view' => 'Ver utilidad y métricas financieras',
        ];

        foreach ($definitions as $code => $description) {
            Permission::updateOrCreate(
                ['code_permission' => $code],
                ['description_permission' => $description]
            );
        }

        $permissions = Permission::pluck('permission_id', 'code_permission');

        $adminRoleIds = DB::table('rol')
            ->whereIn(DB::raw('UPPER(type_rol)'), ['ADMIN', 'ADMINISTRADOR'])
            ->pluck('rol_id')
            ->all();

        $managerRoleIds = DB::table('rol')
            ->where(DB::raw('UPPER(type_rol)'), 'GERENTE')
            ->pluck('rol_id')
            ->all();

        $cashierRoleIds = DB::table('rol')
            ->where(DB::raw('UPPER(type_rol)'), 'CAJERO')
            ->pluck('rol_id')
            ->all();

        $roleDefaults = [
            'ADMINISTRADOR' => [
                // Configuración
                'settings.profile.edit',
                'branch.switch',
                'branch.create',

                // Usuarios
                'users.create',
                'users.create_admin',
                'users.create_manager',
                'users.create_cashier',
                'users.edit',
                'users.edit_admin',
                'users.delete',
                'users.delete_admin',
                'users.assign_branch',
                'permissions.manage',

                // Dashboard
                'dashboard.view',

                // Catálogo
                'catalog.view',
                'catalog.products.create',
                'catalog.products.edit',
                'catalog.products.delete',
                'catalog.services.create',
                'catalog.services.edit',
                'catalog.services.delete',
                'catalog.categories.create',
                'catalog.categories.edit',
                'catalog.categories.delete',
                'catalog.mass_import',

                // Inventario
                'inventory.view',
                'inventory.adjust',
                'inventory.history.view',

                // Ventas
                'sales.view',
                'sales.create',
                'sales.cancel',
                'sales.pos.use',
                'sales.ticket.print',
                'cash.open',
                'cash.close',
                'cash.history.view',

                // Reportes
                'reports.view',
                'reports.export',
                'reports.profit.view',
            ],

            'GERENTE' => [
                // Dashboard
                'dashboard.view',

                // Usuarios
                'users.create',
                'users.create_cashier',
                'users.edit',
                'users.delete',

                // Catálogo
                'catalog.view',
                'catalog.products.create',
                'catalog.products.edit',
                'catalog.services.create',
                'catalog.services.edit',
                'catalog.categories.create',
                'catalog.categories.edit',

                // Inventario
                'inventory.view',
                'inventory.adjust',
                'inventory.history.view',

                // Ventas
                'sales.view',
                'sales.create',
                'sales.cancel',
                'sales.pos.use',
                'sales.ticket.print',
                'cash.open',
                'cash.close',
                'cash.history.view',

                // Reportes
                'reports.view',
                'reports.export',
            ],

            'CAJERO' => [
                // Dashboard
                'dashboard.view',

                // Catálogo
                'catalog.view',

                // Inventario
                'inventory.view',

                // Ventas
                'sales.view',
                'sales.create',
                'sales.pos.use',
                'sales.ticket.print',
                'cash.open',
                'cash.close',
            ],
        ];

        foreach ($adminRoleIds as $roleId) {
            $this->syncRolePermissions($roleId, $roleDefaults['ADMINISTRADOR'], $permissions);
            $this->syncGrants($roleId, array_keys($definitions), $permissions);
        }

        foreach ($managerRoleIds as $roleId) {
            $this->syncRolePermissions($roleId, $roleDefaults['GERENTE'], $permissions);

            $this->syncGrants($roleId, [
                'users.create_cashier',
                'users.edit',
                'users.delete',
            ], $permissions);
        }

        foreach ($cashierRoleIds as $roleId) {
            $this->syncRolePermissions($roleId, $roleDefaults['CAJERO'], $permissions);
            $this->syncGrants($roleId, [], $permissions);
        }
    }

    private function syncRolePermissions(int $roleId, array $allowedCodes, $permissions): void
    {
        foreach ($permissions as $code => $permissionId) {
            DB::table('role_permission')->updateOrInsert(
                [
                    'rol_idfk' => $roleId,
                    'permission_idfk' => $permissionId,
                ],
                [
                    'allow' => in_array($code, $allowedCodes, true) ? 1 : 0,
                ]
            );
        }
    }

    private function syncGrants(int $roleId, array $grantableCodes, $permissions): void
    {
        DB::table('permission_grant')
            ->where('granter_rol_idfk', $roleId)
            ->delete();

        foreach ($grantableCodes as $code) {
            if (!isset($permissions[$code])) {
                continue;
            }

            DB::table('permission_grant')->insert([
                'granter_rol_idfk' => $roleId,
                'permission_idfk' => $permissions[$code],
            ]);
        }
    }
}