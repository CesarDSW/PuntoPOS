<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BranchContext
{
    public const SESSION_KEY = 'current_branch_id';

    public static function canSwitch(User $user): bool
    {
        return UserAccess::has($user, 'branch.switch');
    }

    public static function allowedBranches(User $user): Collection
    {
        $companyId = (int) $user->company_idfk;

        if (!$companyId) {
            return collect();
        }

        if (self::canSwitch($user)) {
            return DB::table('branch')
                ->where('company_idfk', $companyId)
                ->orderBy('name_branch')
                ->get([
                    'branch_id',
                    'name_branch',
                    'address',
                    'city',
                    'state',
                    'phone',
                    'responsible',
                    'email',
                ]);
        }

        return DB::table('branch as b')
            ->join('user_branch as ub', 'ub.branch_idfk', '=', 'b.branch_id')
            ->where('b.company_idfk', $companyId)
            ->where('ub.userr_idfk', $user->userr_id)
            ->orderBy('b.name_branch')
            ->select([
                'b.branch_id',
                'b.name_branch',
                'b.address',
                'b.city',
                'b.state',
                'b.phone',
                'b.responsible',
                'b.email',
            ])
            ->get();
    }

    public static function current(User $user): ?object 
    {
        $branches = self::allowedBranches($user);

        if ($branches->isEmpty()) {
            session()->forget(self::SESSION_KEY);
            return null;
        }

        $currentId = session(self::SESSION_KEY);

        $existsInAllowed = $branches->contains(function ($branch) use ($currentId) {
            return (int) $branch->branch_id === (int) $currentId;
        });

        if (!$existsInAllowed) {
            $currentId = (int) $branches->first()->branch_id;
            session([self::SESSION_KEY => $currentId]);
        }

        return $branches->firstWhere('branch_id', $currentId);
    }

    public static function currentId(User $user): ?int 
    {
        $current = self::current($user);
        return $current ? (int) $current->branch_id : null;
    }

    public static function set(User $user, int $branchId): void 
    {
        $branches = self::allowedBranches($user);

        $exists = $branches->contains(function ($branch) use ($branchId) {
            return (int) $branch->branch_id === (int) $branchId;
        });

        if (!$exists) {
            throw ValidationException::withMessages([
                'branch_id' => ['No tienes permiso para seleccionar esa sucursal.'],
            ]);
        }

        session([self::SESSION_KEY => $branchId]);
    }

    public static function clear(): void 
    {
        session()->forget(self::SESSION_KEY);
    }
}