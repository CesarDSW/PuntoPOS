<?php

namespace App\Http\Controllers\Api\Catalogo;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CatalogBaseController extends Controller
{
    protected function getAuthenticatedUser()
    {
        $user = Auth::user();

        if (!$user) {
            throw ValidationException::withMessages([
                'auth' => ['Usuario no autenticado.'],
            ]);
        }

        if (!$user->company_idfk) {
            throw ValidationException::withMessages([
                'company' => ['El usuario autenticado no tiene una empresa asignada.'],
            ]);
        }

        return $user;
    }

    protected function getCompanyId(): int
    {
        return (int) $this->getAuthenticatedUser()->company_idfk;
    }

    protected function getBranchOrFail(int $branchId, int $companyId)
    {
        $branch = DB::table('branch')
            ->where('branch_id', $branchId)
            ->where('company_idfk', $companyId)
            ->first();

        if (!$branch) {
            throw ValidationException::withMessages([
                'branch_id' => ['La sucursal no pertenece a la empresa del usuario.'],
            ]);
        }

        return $branch;
    }

    protected function resolveBranchId(?int $requestBranchId, int $companyId): ?int
    {
        if ($requestBranchId) {
            $this->getBranchOrFail($requestBranchId, $companyId);
            return $requestBranchId;
        }

        $sessionBranchId = session('current_branch_id');

        if ($sessionBranchId) {
            $exists = DB::table('branch')
                ->where('branch_id', $sessionBranchId)
                ->where('company_idfk', $companyId)
                ->exists();

            if ($exists) {
                return (int) $sessionBranchId;
            }
        }

        $firstBranchId = DB::table('branch')
            ->where('company_idfk', $companyId)
            ->orderBy('branch_id')
            ->value('branch_id');

        if ($firstBranchId) {
            session(['current_branch_id' => (int) $firstBranchId]);
            return (int) $firstBranchId;
        }

        return null;
    }

    protected function getCategoryOrFail(int $categoryId, int $companyId)
    {
        $category = DB::table('category')
            ->where('category_id', $categoryId)
            ->where(function ($q) use ($companyId) {
                $q->where('company_idfk', $companyId)
                  ->orWhereNull('company_idfk');
            })
            ->first();

        if (!$category) {
            throw ValidationException::withMessages([
                'category_id' => ['La categoría no pertenece a la empresa o no existe.'],
            ]);
        }

        return $category;
    }

    protected function getOwnedCategoryOrFail(int $categoryId, int $companyId)
    {
        $category = DB::table('category')
            ->where('category_id', $categoryId)
            ->where('company_idfk', $companyId)
            ->first();

        if (!$category) {
            throw ValidationException::withMessages([
                'category_id' => ['La categoría no pertenece a la empresa del usuario.'],
            ]);
        }

        return $category;
    }

    protected function normalizeStatusValue($value, bool $default = true): int
    {
        if ($value === null || $value === '') {
            return $default ? 1 : 0;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        $value = strtolower(trim((string) $value));

        if (in_array($value, ['1', 'true', 'activo', 'activos', 'active'], true)) {
            return 1;
        }

        if (in_array($value, ['0', 'false', 'inactivo', 'inactivos', 'inactive'], true)) {
            return 0;
        }

        return $default ? 1 : 0;
    }

    protected function syncProductGlobalStatus(int $productId): void
    {
        $hasActiveBranch = DB::table('branch_product_stock')
            ->where('product_idfk', $productId)
            ->where('status_stock', 1)
            ->exists();

        DB::table('productt')
            ->where('product_id', $productId)
            ->update([
                'status_product' => $hasActiveBranch ? 1 : 0,
            ]);
    }
}