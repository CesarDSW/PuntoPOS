<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserAccess
{
    // Relaciona el rol con el tipo de usuario
    public static function normalizeRoleName(?string $roleName): string
    {
        $name = strtoupper(trim((string) $roleName));

        return match ($name) {
            'ADMIN', 'ADMINISTRADOR' => 'ADMINISTRADOR',
            'GERENTE' => 'GERENTE',
            'CAJERO' => 'CAJERO',
            default => $name,
        };
    }

    // Manda a llamar al rol dependiendo del tipo de usuario
    public static function roleName(User $user): string
    {
        $roleName = null;

        if (method_exists($user, 'role')) {
            $roleName = optional($user->role)->type_rol;
        }

        if (!$roleName && !empty($user->rol_idfk)) {
            $roleName = DB::table('rol')
                ->where('rol_id', $user->rol_idfk)
                ->value('type_rol');
        }
        return self::normalizeRoleName($roleName);
    }

    // Si es el dueño, los permisos no pueden ser cambiados
    public static function isOwner(User $user): bool
    {
        if (!$user->company_idfk) {
            return false;
        }

        $ownerId = DB::table('company')
            ->where('company_id', $user->company_idfk)
            ->value('owner_user_id');

        return (int) $ownerId === (int) $user->userr_id;
    }

    public static function allPermissions(): Collection
    {
        return Permission::orderBy('description_permission')->get();
    }

    public static function permissionIdByCode(string $code): ?int
    {
        $id = Permission::where('code_permission', $code)->value('permission_id');
        return $id ? (int) $id : null;
    }

    // Detecta el rol de un usuario y que permisos puede usar
    public static function has(User $user, string $code): bool
    {
        if (self::isOwner($user)) {
            return true;
        }

        $permissionId = self::permissionIdByCode($code);

        if (!$permissionId) {
            return false;
        }

        $userOverride = DB::table('user_permission')
            ->where('userr_idfk', $user->userr_id)
            ->where('permission_idfk', $permissionId)
            ->value('allow');

        if ($userOverride !== null) {
            return (bool) $userOverride;
        }

        $roleAllow = DB::table('role_permission')
            ->where('rol_idfk', $user->rol_idfk)
            ->where('permission_idfk', $permissionId)
            ->value('allow');

        return (bool) $roleAllow;
    }

    // Funcion para otorgar los permisos a un usuario
    public static function canGrant(User $actor, string $code): bool
    {
        if (self::isOwner($actor)) {
            return true;
        }

        $permissionId = self::permissionIdByCode($code);

        if (!$permissionId) {
            return false;
        }

        return DB::table('permission_grant')
            ->where('granter_rol_idfk', $actor->rol_idfk)
            ->where('permission_idfk', $permissionId)
            ->exists();
    }

    // Funcion para otorgar permisos
    public static function grantablePermissions(User $actor): Collection
    {
        return self::allPermissions()->filter(function ($permission) use ($actor) {
            return self::canGrant($actor, $permission->code_permission);
        })->values();
    }

    public static function userOverrideStates(int $userId): array
    {
        $states = [];

        foreach (self::allPermissions() as $permission) {
            $states[$permission->code_permission] = 'inherit';
        }

        $rows = DB::table('user_permission as up')
            ->join('permission as p', 'p.permission_id', '=', 'up.permission_idfk')
            ->where('up.userr_idfk', $userId)
            ->select('p.code_permission', 'up.allow')
            ->get();

        foreach ($rows as $row) {
            $states[$row->code_permission] = ((int) $row->allow === 1) ? 'allow' : 'deny';
        }

        return $states;
    }

    public static function syncUserOverrides(User $actor, User $target, array $states): void
    {
        foreach (self::grantablePermissions($actor) as $permission) {
            $code = $permission->code_permission;
            $state = $states[$code] ?? 'inherit';

            DB::table('user_permission')
                ->where('userr_idfk', $target->userr_id)
                ->where('permission_idfk', $permission->permission_id)
                ->delete();

            if ($state === 'allow' || $state === 'deny') {
                DB::table('user_permission')->updateOrInsert(
                    [
                        'userr_idfk' => $target->userr_id,
                        'permission_idfk' => $permission->permission_id,
                    ],
                    [
                        'allow' => $state === 'allow' ? 1 : 0,
                    ]
                );
            }
        }
    }

    // Permisos para crear usuarios 
    public static function canCreateRole(User $actor, string $targetRoleName): bool
    {
        if (!self::has($actor, 'users.create')) {
            return false;
        }

        return match (self::normalizeRoleName($targetRoleName)) {
            'ADMINISTRADOR' => self::has($actor, 'users.create_admin'),
            'GERENTE' => self::has($actor, 'users.create_manager'),
            'CAJERO' => self::has($actor, 'users.create_cashier'),
            default => false,
        };
    }

    public static function canEditTarget(User $actor, User $target): bool
    {
        if (!self::has($actor, 'users.edit')) {
            return false;
        }

        $isSelf = (int) $actor->userr_id === (int) $target->userr_id;
        if ($isSelf) {
            return true;
        }

        if (self::isOwner($target)) {
            return false;
        }

        $targetRole = self::roleName($target);

        if ($targetRole === 'ADMINISTRADOR' && !self::has($actor, 'users.edit_admin')) {
            return false;
        }

        return true;
    }

    public static function canDeleteTarget(User $actor, User $target): bool
    {
        if (!self::has($actor, 'users.delete')) {
            return false;
        }

        $isSelf = (int) $actor->userr_id === (int) $target->userr_id;
        if ($isSelf) {
            return false;
        }

        if (self::isOwner($target)) {
            return false;
        }

        $targetRole = self::roleName($target);

        if ($targetRole === 'ADMINISTRADOR' && !self::has($actor, 'users.delete_admin')) {
            return false;
        }

        return true;
    }

    // Permisos para sucursal
    public static function canAssignBranch(User $actor): bool
    {
        return self::has($actor, 'users.assign_branch');
    }

    public static function canManagePermissions(User $actor): bool
    {
        return self::has($actor, 'permissions.manage');
    }

    public static function canCreateBranch(User $user): bool 
    {
        return self::has($user, 'branch.create');
    }
    
    // Permisos para el catalogo
    public static function canViewCatalog(User $user): bool
    {
        return self::has($user, 'catalog.view');
    }

    public static function canCreateProduct(User $user): bool
    {
        return self::has($user, 'catalog.products.create');
    }

    public static function canEditProduct(User $user): bool
    {
        return self::has($user, 'catalog.products.edit');
    }

    public static function canDeleteProduct(User $user): bool
    {
        return self::has($user, 'catalog.products.delete');
    }

    public static function canCreateService(User $user): bool
    {
        return self::has($user, 'catalog.services.create');
    }

    public static function canEditService(User $user): bool
    {
        return self::has($user, 'catalog.services.edit');
    }

    public static function canDeleteService(User $user): bool
    {
        return self::has($user, 'catalog.services.delete');
    }

    public static function canCreateCategory(User $user): bool
    {
        return self::has($user, 'catalog.categories.create');
    }

    public static function canEditCategory(User $user): bool
    {
        return self::has($user, 'catalog.categories.edit');
    }

    public static function canDeleteCategory(User $user): bool
    {
        return self::has($user, 'catalog.categories.delete');
    }

    public static function canMassImportCatalog(User $user): bool
    {
        return self::has($user, 'catalog.mass_import');
    }

    // Permisos de inventario
    public static function canViewInventory(User $user): bool 
    {
        return self::has($user, 'inventory.view');
    }

    public static function canAdjustInventory(User $user): bool 
    {
        return self::has($user, 'inventory.adjust');
    }

    public static function canViewInventoryHistory(User $user): bool 
    {
        return self::has($user, 'inventory.history.view');
    }

    public static function canViewSales(User $user): bool
    {
        return self::has($user, 'sales.view');
    }

    public static function canCreateSales(User $user): bool
    {
        return self::has($user, 'sales.create');
    }

    public static function canUsePos(User $user): bool
    {
        return self::has($user, 'sales.pos.use');
    }

    public static function canPrintSaleTicket(User $user): bool
    {
        return self::has($user, 'sales.ticket.print');
    }

    public static function canOpenCash(User $user): bool
    {
        return self::has($user, 'cash.open');
    }

    public static function canCloseCash(User $user): bool
    {
        return self::has($user, 'cash.close');
    }

    public static function canViewCashHistory(User $user): bool
    {
        return self::has($user, 'cash.history.view');
    }

    public static function canViewReports(User $user): bool
    {
        return self::has($user, 'reports.view');
    }

    public static function canExportReports(User $user): bool
    {
        return self::has($user, 'reports.export');
    }

    public static function canViewProfitReports(User $user): bool
    {
        return self::has($user, 'reports.profit.view');
    }
    
    public static function summary(User $user): array
    {
        return [
            'can_create_users' => self::has($user, 'users.create'),
            'can_create_branch' => self::canCreateBranch($user),
            'can_create_admin' => self::canCreateRole($user, 'ADMINISTRADOR'),
            'can_create_manager' => self::canCreateRole($user, 'GERENTE'),
            'can_create_cashier' => self::canCreateRole($user, 'CAJERO'),
            'can_edit_users' => self::has($user, 'users.edit'),
            'can_edit_admin' => self::has($user, 'users.edit_admin'),
            'can_delete_users' => self::has($user, 'users.delete'),
            'can_delete_admin' => self::has($user, 'users.delete_admin'),
            'can_assign_branch' => self::canAssignBranch($user),
            'can_manage_permissions' => self::canManagePermissions($user),
            'can_edit_business_profile' => self::has($user, 'settings.profile.edit'),
            'can_switch_branch' => self::has($user, 'branch.switch'),
        ];
    }
}