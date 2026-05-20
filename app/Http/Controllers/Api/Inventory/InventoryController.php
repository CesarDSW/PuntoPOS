<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\UserAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    private function getAuthenticatedUser()
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

    private function authorizeInventoryView(): void
    {
        $user = Auth::user();

        if (!$user || !UserAccess::has($user, 'inventory.view')) {
            abort(403, 'No autorizado para ver inventario.');
        }
    }

    private function getBranchOrFail(int $branchId, int $companyId)
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

    private function resolveBranchId(?int $requestBranchId, int $companyId): ?int
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

    public function summary(Request $request)
    {
        $this->authorizeInventoryView();

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
        ]);

        $user = $this->getAuthenticatedUser();
        $companyId = (int) $user->company_idfk;

        $branchId = $this->resolveBranchId(
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            $companyId
        );

        $query = DB::table('branch_product_stock as bps')
            ->join('branch as b', 'b.branch_id', '=', 'bps.branch_idfk')
            ->join('productt as p', 'p.product_id', '=', 'bps.product_idfk')
            ->where('b.company_idfk', $companyId);

        if ($branchId) {
            $query->where('bps.branch_idfk', $branchId);
        }

        $summary = (clone $query)
            ->selectRaw('
                COUNT(DISTINCT bps.product_idfk) as total_products,
                COALESCE(SUM(bps.stocks), 0) as total_stock_units,
                COALESCE(SUM(CASE WHEN bps.stocks <= bps.minimum_stock THEN 1 ELSE 0 END), 0) as low_stock_count,
                COALESCE(SUM(bps.stocks * p.price), 0) as inventory_value
            ')
            ->first();

        return response()->json([
            'branch_id' => $branchId,
            'total_products'    => (int) ($summary->total_products ?? 0),
            'total_stock_units' => (int) ($summary->total_stock_units ?? 0),
            'low_stock_count'   => (int) ($summary->low_stock_count ?? 0),
            'inventory_value'   => (float) ($summary->inventory_value ?? 0),
        ]);
    }

    public function lowStock(Request $request)
    {
        $this->authorizeInventoryView();

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
            'limit'     => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $user = $this->getAuthenticatedUser();
        $companyId = (int) $user->company_idfk;
        $limit = (int) ($validated['limit'] ?? 10);

        $branchId = $this->resolveBranchId(
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            $companyId
        );

        $query = DB::table('branch_product_stock as bps')
            ->join('branch as b', 'b.branch_id', '=', 'bps.branch_idfk')
            ->join('productt as p', 'p.product_id', '=', 'bps.product_idfk')
            ->leftJoin('category as c', 'c.category_id', '=', 'p.category_idfk')
            ->where('b.company_idfk', $companyId)
            ->whereColumn('bps.stocks', '<=', 'bps.minimum_stock');

        if ($branchId) {
            $query->where('bps.branch_idfk', $branchId);
        }

        $items = $query
            ->select([
                'p.product_id',
                'p.name_product',
                'p.code_product',
                'p.description_product',
                'p.price',
                'c.category_id',
                'c.name_category',
                'b.branch_id',
                'b.name_branch',
                'bps.stocks',
                'bps.minimum_stock',
                'bps.status_stock',
                DB::raw("
                    CASE
                        WHEN bps.stocks = 0 THEN 'out'
                        WHEN bps.stocks <= bps.minimum_stock THEN 'low'
                        ELSE 'normal'
                    END as stock_alert
                "),
            ])
            ->orderBy('bps.stocks', 'asc')
            ->orderBy('p.name_product', 'asc')
            ->limit($limit)
            ->get();

        return response()->json($items);
    }

    public function index(Request $request)
    {
        $this->authorizeInventoryView();

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
            'search'    => ['nullable', 'string', 'max:100'],
            'status'    => ['nullable', 'in:all,normal,low,out'],
            'page'      => ['nullable', 'integer', 'min:1'],
            'per_page'  => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $user = $this->getAuthenticatedUser();
        $companyId = (int) $user->company_idfk;
        $search = $validated['search'] ?? null;
        $status = $validated['status'] ?? 'all';
        $perPage = (int) ($validated['per_page'] ?? 10);

        $branchId = $this->resolveBranchId(
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            $companyId
        );

        $query = DB::table('branch_product_stock as bps')
            ->join('branch as b', 'b.branch_id', '=', 'bps.branch_idfk')
            ->join('productt as p', 'p.product_id', '=', 'bps.product_idfk')
            ->leftJoin('category as c', 'c.category_id', '=', 'p.category_idfk')
            ->where('b.company_idfk', $companyId)
            ->select([
                'p.product_id',
                'p.name_product',
                'p.code_product',
                'p.description_product',
                'p.price',
                'c.category_id',
                'c.name_category',
                'b.branch_id',
                'b.name_branch',
                'bps.stocks',
                'bps.minimum_stock',
                'bps.status_stock',
                DB::raw("
                    CASE
                        WHEN bps.stocks = 0 THEN 'out'
                        WHEN bps.stocks <= bps.minimum_stock THEN 'low'
                        ELSE 'normal'
                    END as stock_status
                "),
            ]);

        if ($branchId) {
            $query->where('bps.branch_idfk', $branchId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('p.name_product', 'like', "%{$search}%")
                  ->orWhere('p.code_product', 'like', "%{$search}%");
            });
        }

        if ($status === 'low') {
            $query->where('bps.stocks', '>', 0)
                  ->whereColumn('bps.stocks', '<=', 'bps.minimum_stock');
        } elseif ($status === 'normal') {
            $query->whereColumn('bps.stocks', '>', 'bps.minimum_stock');
        } elseif ($status === 'out') {
            $query->where('bps.stocks', 0);
        }

        $inventory = $query
            ->orderBy('p.name_product')
            ->paginate($perPage);

        return response()->json($inventory);
    }

    public function show(Request $request, int $productId)
    {
        $this->authorizeInventoryView();

        $user = $this->getAuthenticatedUser();
        $companyId = (int) $user->company_idfk;

        $branchId = $this->resolveBranchId(
            $request->filled('branch_id') ? (int) $request->branch_id : null,
            $companyId
        );

        if (!$branchId) {
            throw ValidationException::withMessages([
                'branch_id' => ['No hay una sucursal seleccionada ni registrada para esta empresa.'],
            ]);
        }

        $product = DB::table('branch_product_stock as bps')
            ->join('productt as p', 'p.product_id', '=', 'bps.product_idfk')
            ->leftJoin('category as c', 'c.category_id', '=', 'p.category_idfk')
            ->join('branch as b', 'b.branch_id', '=', 'bps.branch_idfk')
            ->where('bps.branch_idfk', $branchId)
            ->where('p.product_id', $productId)
            ->where('p.company_idfk', $companyId)
            ->select([
                'p.product_id',
                'p.name_product',
                'p.code_product',
                'p.description_product',
                'p.price',
                'c.name_category',
                'b.branch_id',
                'b.name_branch',
                'bps.stocks as current_stock',
                'bps.minimum_stock',
                DB::raw("
                    CASE
                        WHEN bps.stocks = 0 THEN 'out'
                        WHEN bps.stocks <= bps.minimum_stock THEN 'low'
                        ELSE 'normal'
                    END as stock_status
                "),
            ])
            ->first();

        if (!$product) {
            throw ValidationException::withMessages([
                'product_id' => ['El producto no existe en la sucursal seleccionada.'],
            ]);
        }

        return response()->json($product);
    }

    public function reasons(Request $request)
    {
        $this->authorizeInventoryView();

        $typeInput = strtoupper(trim((string) $request->query('type', 'ENTRADA')));

        $type = match ($typeInput) {
            'IN', 'ENTRADA' => 'ENTRADA',
            'OUT', 'SALIDA' => 'SALIDA',
            default => 'ENTRADA',
        };

        $reasons = $type === 'ENTRADA'
            ? ['Compra', 'Devolución de cliente', 'Ajuste positivo', 'Transferencia recibida', 'Otro']
            : ['Venta', 'Merma', 'Producto dañado', 'Ajuste negativo', 'Otro'];

        return response()->json([
            'type' => $type,
            'reasons' => $reasons,
        ]);
    }
}