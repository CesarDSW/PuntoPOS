<?php

namespace App\Http\Controllers\Api\Catalogo;

use App\Support\CompanyPreference;
use App\Support\UserAccess;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CatalogController extends CatalogBaseController
{
    public function summary()
    {
        $this->authorizeCatalogView();

        $companyId = $this->getCompanyId();

        $productsCount = DB::table('productt')
            ->where('company_idfk', $companyId)
            ->count();

        $servicesCount = DB::table('servicee')
            ->where('company_idfk', $companyId)
            ->count();

        $activeProducts = DB::table('productt')
            ->where('company_idfk', $companyId)
            ->where('status_product', 1)
            ->count();

        $activeServices = DB::table('servicee')
            ->where('company_idfk', $companyId)
            ->where('status_service', 1)
            ->count();

        return response()->json([
            'total_items' => $productsCount + $servicesCount,
            'products_count' => $productsCount,
            'services_count' => $servicesCount,
            'active_count' => $activeProducts + $activeServices,
        ]);
    }

    public function items(Request $request)
    {
        $this->authorizeCatalogView();

        $validated = $request->validate([
            'branch_id'   => ['nullable', 'integer', 'exists:branch,branch_id'],
            'type'        => ['nullable', 'in:all,product,service'],
            'category_id' => ['nullable', 'integer', 'exists:category,category_id'],
            'status'      => ['nullable', 'in:all,active,inactive'],
            'search'      => ['nullable', 'string', 'max:100'],
            'page'        => ['nullable', 'integer', 'min:1'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $companyId = $this->getCompanyId();

        $branchId = $this->resolveBranchId(
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            $companyId
        );

        $type = $validated['type'] ?? 'all';
        $categoryId = isset($validated['category_id']) ? (int) $validated['category_id'] : null;
        $status = $validated['status'] ?? 'all';
        $search = trim($validated['search'] ?? '');
        $perPage = (int) ($validated['per_page'] ?? 20);
        $page = (int) ($validated['page'] ?? LengthAwarePaginator::resolveCurrentPage());

        $products = collect();
        $services = collect();

        $stockSubquery = DB::table('branch_product_stock')
            ->where('status_stock', 1)
            ->select(
                'product_idfk',
                DB::raw('SUM(stocks) as total_stock')
            )
            ->groupBy('product_idfk');

        if (in_array($type, ['all', 'product'], true)) {
            $productQuery = DB::table('productt as p')
                ->leftJoin('category as c', 'c.category_id', '=', 'p.category_idfk')
                ->leftJoinSub($stockSubquery, 'st', function ($join) {
                    $join->on('st.product_idfk', '=', 'p.product_id');
                })
                ->leftJoin('branch_product_stock as bps_current', function ($join) use ($branchId) {
                    $join->on('bps_current.product_idfk', '=', 'p.product_id');

                    if ($branchId) {
                        $join->where('bps_current.branch_idfk', '=', $branchId);
                    } else {
                        $join->whereRaw('1 = 0');
                    }
                })
                ->where('p.company_idfk', $companyId)
                ->select([
                    'p.product_id as item_id',
                    DB::raw("'product' as item_type"),
                    'p.name_product as name',
                    'p.code_product as code',
                    'p.description_product as description',
                    'p.category_idfk as category_id',
                    'c.name_category as category_name',
                    'p.price',
                    'p.cost',
                    DB::raw("
                        CASE
                            WHEN p.status_product = 0 THEN 0
                            WHEN bps_current.status_stock IS NULL THEN p.status_product
                            WHEN bps_current.status_stock = 0 THEN 0
                            ELSE 1
                        END as status
                    "),
                    DB::raw('COALESCE(st.total_stock, 0) as stock'),
                    DB::raw('COALESCE(bps_current.minimum_stock, 0) as minimum_stock'),
                ]);

            if ($categoryId) {
                $productQuery->where('p.category_idfk', $categoryId);
            }

            if ($status === 'active') {
                $productQuery->where(function ($q) {
                    $q->where('p.status_product', 1)
                        ->where(function ($q2) {
                            $q2->whereNull('bps_current.status_stock')
                                ->orWhere('bps_current.status_stock', 1);
                        });
                });
            } elseif ($status === 'inactive') {
                $productQuery->where(function ($q) {
                    $q->where('p.status_product', 0)
                        ->orWhere('bps_current.status_stock', 0);
                });
            }

            if ($search !== '') {
                $productQuery->where(function ($q) use ($search) {
                    $q->where('p.name_product', 'like', "%{$search}%")
                        ->orWhere('p.code_product', 'like', "%{$search}%")
                        ->orWhere('p.description_product', 'like', "%{$search}%");
                });
            }

            $products = $productQuery->get()->map(function ($row) use ($companyId) {
                $row->status_label = ((int) $row->status === 1) ? 'activo' : 'inactivo';
                $row->stock_display = ((int) $row->stock) . ' unidades';

                $row->price_display = CompanyPreference::formatMoneyForCompany(
                    $companyId,
                    $row->price ?? 0
                );

                $row->cost_display = $row->cost !== null
                    ? CompanyPreference::formatMoneyForCompany($companyId, $row->cost)
                    : null;

                return $row;
            });
        }

        if (in_array($type, ['all', 'service'], true)) {
            $serviceQuery = DB::table('servicee as s')
                ->leftJoin('category as c', 'c.category_id', '=', 's.category_idfk')
                ->where('s.company_idfk', $companyId)
                ->select([
                    's.service_id as item_id',
                    DB::raw("'service' as item_type"),
                    's.name_service as name',
                    's.code_service as code',
                    's.description_service as description',
                    's.category_idfk as category_id',
                    'c.name_category as category_name',
                    's.price',
                    DB::raw('NULL as cost'),
                    's.status_service as status',
                    DB::raw('NULL as stock'),
                    DB::raw('NULL as minimum_stock'),
                ]);

            if ($categoryId) {
                $serviceQuery->where('s.category_idfk', $categoryId);
            }

            if ($status === 'active') {
                $serviceQuery->where('s.status_service', 1);
            } elseif ($status === 'inactive') {
                $serviceQuery->where('s.status_service', 0);
            }

            if ($search !== '') {
                $serviceQuery->where(function ($q) use ($search) {
                    $q->where('s.name_service', 'like', "%{$search}%")
                        ->orWhere('s.code_service', 'like', "%{$search}%")
                        ->orWhere('s.description_service', 'like', "%{$search}%");
                });
            }

            $services = $serviceQuery->get()->map(function ($row) use ($companyId) {
                $row->status_label = ((int) $row->status === 1) ? 'activo' : 'inactivo';
                $row->stock_display = 'N/A';

                $row->price_display = CompanyPreference::formatMoneyForCompany(
                    $companyId,
                    $row->price ?? 0
                );

                $row->cost_display = null;

                return $row;
            });
        }

        /** @var Collection $merged */
        $merged = $products
            ->concat($services)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $total = $merged->count();

        $items = $merged
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return response()->json($paginator);
    }

    private function authorizeCatalogView(): void
    {
        $user = Auth::user();

        if (!$user || !UserAccess::has($user, 'catalog.view')) {
            abort(403, 'No autorizado para ver el catálogo.');
        }
    }
}