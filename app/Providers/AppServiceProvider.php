<?php

namespace App\Providers;

use App\Models\User;
use App\Support\BranchContext;
use App\Support\CompanyPreference;
use App\Support\UserAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layout.dashboard_design', function ($view) {
            $user = Auth::user();
            $companyId = Auth::check() ? (int) Auth::user()->company_idfk : null;

            if (!$user) {
                $view->with('topbarBranchContext', [
                    'can_switch' => false,
                    'branches' => collect(),
                    'current_branch' => null,
                ]);

                $view->with('topbarBranchCreateContext', [
                    'can_create' => false,
                    'manager_users' => collect(),
                    'branch_count' => 0,
                    'is_first_branch' => false,
                    'has_managers' => false,
                ]);

                return;
            }

            $view->with('uiPreferences', CompanyPreference::all($companyId));

            $allowedBranches = BranchContext::allowedBranches($user);
            $currentBranch = BranchContext::current($user);

            $view->with('topbarBranchContext', [
                'can_switch' => BranchContext::canSwitch($user),
                'branches' => $allowedBranches,
                'current_branch' => $currentBranch,
            ]);

            $managerUsers = User::query()
                ->join('rol', 'rol.rol_id', '=', 'userr.rol_idfk')
                ->where('userr.company_idfk', $companyId)
                ->whereRaw("UPPER(rol.type_rol) = 'GERENTE'")
                ->orderBy('userr.name_user')
                ->get([
                    'userr.userr_id',
                    'userr.name_user',
                    'userr.email',
                ]);

            $branchCount = (int) \Illuminate\Support\Facades\DB::table('branch')
                ->where('company_idfk', $companyId)
                ->count();

            $view->with('topbarBranchCreateContext', [
                'can_create' => UserAccess::has($user, 'branch.create'),
                'manager_users' => $managerUsers,
                'branch_count' => $branchCount,
                'is_first_branch' => $branchCount === 0,
                'has_managers' => $managerUsers->isNotEmpty(),
            ]);
        });
    }
}