<?php

namespace App\Http\Controllers\Api\Ventas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosController extends SalesBaseController
{
    public function status(Request $request)
    {
        $companyId = $this->getCompanyId();
        $userId = $this->getUserId();

        $branchId = $this->resolveBranchId(
            $request->filled('branch_id') ? (int) $request->branch_id : null,
            $companyId
        );

        if (!$branchId) {
            throw ValidationException::withMessages([
                'branch_id' => ['Debes tener una sucursal seleccionada.'],
            ]);
        }

        $branch = $this->getBranchOrFail($branchId, $companyId);
        $user = $this->getAuthenticatedUser();

        $cashSession = $this->getOpenCashSession($companyId);
        $activeShift = $this->getOpenShift($companyId, $branchId, $userId);
        $systemShift = $this->getAnyOpenShift($companyId);

        return response()->json([
            'branch' => [
                'branch_id' => $branch->branch_id,
                'name_branch' => $branch->name_branch,
            ],
            'user' => [
                'userr_id' => $user->userr_id,
                'name_user' => $user->name_user,
            ],
            'cash_session' => $cashSession,
            'active_shift' => $activeShift,
            'system_shift' => $systemShift,
        ]);
    }

    public function products(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
            'search' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $companyId = $this->getCompanyId();

        $branchId = $this->resolveBranchId(
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            $companyId
        );

        if (!$branchId) {
            throw ValidationException::withMessages([
                'branch_id' => ['Debes tener una sucursal seleccionada.'],
            ]);
        }

        $search = trim($validated['search'] ?? '');
        $limit = (int) ($validated['limit'] ?? 20);

        $query = DB::table('branch_product_stock as bps')
            ->join('productt as p', 'p.product_id', '=', 'bps.product_idfk')
            ->where('p.company_idfk', $companyId)
            ->where('bps.branch_idfk', $branchId)
            ->where('bps.status_stock', 1)
            ->where('p.status_product', 1)
            ->where('bps.stocks', '>', 0)
            ->select([
                'p.product_id',
                'p.name_product',
                'p.code_product',
                'p.price',
                'bps.stocks',
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('p.name_product', 'like', "%{$search}%")
                  ->orWhere('p.code_product', 'like', "%{$search}%");
            });
        }

        $products = $query
            ->orderBy('p.name_product')
            ->limit($limit)
            ->get();

        return response()->json($products);
    }

    public function customers(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $companyId = $this->getCompanyId();
        $search = trim($validated['search'] ?? '');
        $limit = (int) ($validated['limit'] ?? 10);

        $query = DB::table('customer')
            ->where('company_idfk', $companyId)
            ->select([
                'customer_id',
                'name_customer',
                'phone',
                'email',
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name_customer', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query
            ->orderBy('name_customer')
            ->limit($limit)
            ->get();

        return response()->json($customers);
    }
}