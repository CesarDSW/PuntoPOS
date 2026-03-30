<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BranchContextController extends Controller
{
    private function getAuthenticatedUser()
    {
        $user = Auth::user();

        if(!$user){
            throw ValidationException::withMessages([
                'auth' => ['Usuario no autenticado.'],
            ]);
        }

        if(!$user->company_idfk){
            throw ValidationException::withMessages([
                'company' => ['El usuario autenticado no tiene una empresa asignada.']
            ]);
        }

        return $user;
    }

    private function resolveCurrentBranch(int $companyId)
    {
        $sessionBranchId = session('current_branch_id');

        if ($sessionBranchId) {
            $branch = DB::table('branch')
                ->where('branch_id', $sessionBranchId)
                ->where('company_idfk', $companyId)
                ->first();

            if ($branch) {
                return $branch;
            }
        }

        $firstBranch = DB::table('branch')
            ->where('company_idfk', $companyId)
            ->orderBy('branch_id')
            ->first();

        if ($firstBranch) {
            session(['current_branch_id' => (int) $firstBranch->branch_id]);
            return $firstBranch;
        }

        session()->forget('current_branch_id');
        return null;
    }

    public function index()
    {
        $user = $this->getAuthenticatedUser();
        $companyId = (int) $user->company_idfk;

        $branches = DB::table('branch')
            ->where('company_idfk', $companyId)
            ->orderBy('branch_id')
            ->select('branch_id', 'name_branch')
            ->get();

        $currentBranch = $this->resolveCurrentBranch($companyId);

        return response()->json([
            'current_branch_id' => $currentBranch?->branch_id,
            'current_branch_name' => $currentBranch?->name_branch,
            'branches' => $branches,
        ]);
    }

    public function current()
    {
        $user = $this->getAuthenticatedUser();
        $companyId = (int) $user->company_idfk;

        $currentBranch = $this->resolveCurrentBranch($companyId);

        return response()->json([
            'branch_id' => $currentBranch?->branch_id,
            'name_branch' => $currentBranch?->name_branch,
        ]);
    }

    public function update(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branch,branch_id'],
        ]);

        $user = $this->getAuthenticatedUser();
        $companyId = (int) $user->company_idfk;
        $branchId = (int) $validated['branch_id'];

        $branch = DB::table('branch')
            ->where('branch_id', $branchId)
            ->where('company_idfk', $companyId)
            ->first();

        if (!$branch) {
            throw ValidationException::withMessages([
                'branch_id' => ['La sucursal no pertenece a la empresa del usuario.'],
            ]);
        }

        session(['current_branch_id' => (int) $branch->branch_id]);

        return response()->json([
            'message' => 'Sucursal actualizada correctamente.',
            'branch' => [
                'branch_id' => $branch->branch_id,
                'name_branch' => $branch->name_branch,
            ],
        ]);
    }
}
