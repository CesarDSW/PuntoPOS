<?php

/* namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Support\UserAccess;

class GlobalSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $user = $request->user();
        $term = trim((string) $request->query('q', ''));

        if ($term === '' || mb_strlen($term) < 2) {
            return response()->json([
                'query' => $term,
                'results' => [],
            ]);
        }

        $companyId = $this->resolveCompanyId($user);

        if (!$companyId) {
            return response()->json([
                'query' => $term,
                'results' => [],
            ]);
        }

        $normalized = mb_strtolower($term);
        $results = []; */

        /*
        |--------------------------------------------------------------------------
        | Productos
        |--------------------------------------------------------------------------
        */
        /* if (UserAccess::has($user, 'catalog.view')) {
            try {
                $products = DB::table('products as p')
                    ->leftJoin('categories as c', 'c.category_id', '=', 'p.category_id')
                    ->where('p.company_id', $companyId)
                    ->where(function ($query) use ($normalized) {
                        $query->whereRaw('LOWER(COALESCE(p.name_product, "")) LIKE ?', ['%' . $normalized . '%'])
                            ->orWhereRaw('LOWER(COALESCE(p.code, "")) LIKE ?', ['%' . $normalized . '%'])
                            ->orWhereRaw('LOWER(COALESCE(p.description, "")) LIKE ?', ['%' . $normalized . '%']);
                    })
                    ->orderBy('p.name_product')
                    ->limit(8)
                    ->get([
                        'p.product_id',
                        'p.name_product',
                        'p.code',
                        'p.description',
                        'c.name_category as category_name',
                    ]);

                foreach ($products as $product) {
                    $results[] = [
                        'group' => 'Productos',
                        'type' => 'product',
                        'title' => $product->name_product ?: 'Producto sin nombre',
                        'subtitle' => $product->category_name
                            ? 'Categoría: ' . $product->category_name
                            : ($product->description ?: 'Producto del catálogo'),
                        'meta' => $product->code ? 'Código: ' . $product->code : 'Producto',
                        'url' => route('catalog.index') . '?highlight=product-' . $product->product_id,
                    ];
                }
            } catch (\Throwable $e) {
                // Silencioso para no romper la búsqueda global.
            }
        } */

        /*
        |--------------------------------------------------------------------------
        | Servicios
        |--------------------------------------------------------------------------
        */
        /* if (UserAccess::has($user, 'catalog.view')) {
            try {
                $services = DB::table('services as s')
                    ->leftJoin('categories as c', 'c.category_id', '=', 's.category_id')
                    ->where('s.company_id', $companyId)
                    ->where(function ($query) use ($normalized) {
                        $query->whereRaw('LOWER(COALESCE(s.name_service, "")) LIKE ?', ['%' . $normalized . '%'])
                            ->orWhereRaw('LOWER(COALESCE(s.code, "")) LIKE ?', ['%' . $normalized . '%'])
                            ->orWhereRaw('LOWER(COALESCE(s.description, "")) LIKE ?', ['%' . $normalized . '%']);
                    })
                    ->orderBy('s.name_service')
                    ->limit(8)
                    ->get([
                        's.service_id',
                        's.name_service',
                        's.code',
                        's.description',
                        'c.name_category as category_name',
                    ]);

                foreach ($services as $service) {
                    $results[] = [
                        'group' => 'Servicios',
                        'type' => 'service',
                        'title' => $service->name_service ?: 'Servicio sin nombre',
                        'subtitle' => $service->category_name
                            ? 'Categoría: ' . $service->category_name
                            : ($service->description ?: 'Servicio del catálogo'),
                        'meta' => $service->code ? 'Código: ' . $service->code : 'Servicio',
                        'url' => route('catalog.index') . '?highlight=service-' . $service->service_id,
                    ];
                }
            } catch (\Throwable $e) {
                //
            }
        } */

        /*
        |--------------------------------------------------------------------------
        | Categorías
        |--------------------------------------------------------------------------
        */
        /* if (UserAccess::has($user, 'catalog.view')) {
            try {
                $categories = DB::table('categories as c')
                    ->where('c.company_id', $companyId)
                    ->where(function ($query) use ($normalized) {
                        $query->whereRaw('LOWER(COALESCE(c.name_category, "")) LIKE ?', ['%' . $normalized . '%'])
                            ->orWhereRaw('LOWER(COALESCE(c.description, "")) LIKE ?', ['%' . $normalized . '%']);
                    })
                    ->orderBy('c.name_category')
                    ->limit(6)
                    ->get([
                        'c.category_id',
                        'c.name_category',
                        'c.description',
                    ]);

                foreach ($categories as $category) {
                    $results[] = [
                        'group' => 'Categorías',
                        'type' => 'category',
                        'title' => $category->name_category ?: 'Categoría sin nombre',
                        'subtitle' => $category->description ?: 'Categoría del catálogo',
                        'meta' => 'Categoría',
                        'url' => route('catalog.index') . '?highlight=category-' . $category->category_id,
                    ];
                }
            } catch (\Throwable $e) {
                //
            }
        } */

        /*
        |--------------------------------------------------------------------------
        | Clientes
        |--------------------------------------------------------------------------
        */
        /*if (UserAccess::has($user, 'customers.view') || UserAccess::has($user, 'sales.pos.use')) {
            try {
                $customers = DB::table('customers as c')
                    ->where('c.company_id', $companyId)
                    ->where(function ($query) use ($normalized) {
                        $query->whereRaw('LOWER(COALESCE(c.name_customer, "")) LIKE ?', ['%' . $normalized . '%'])
                            ->orWhereRaw('LOWER(COALESCE(c.email, "")) LIKE ?', ['%' . $normalized . '%'])
                            ->orWhereRaw('LOWER(COALESCE(c.phone, "")) LIKE ?', ['%' . $normalized . '%']);
                    })
                    ->orderBy('c.name_customer')
                    ->limit(8)
                    ->get([
                        'c.customer_id',
                        'c.name_customer',
                        'c.email',
                        'c.phone',
                    ]);

                foreach ($customers as $customer) {
                    $subtitleParts = array_filter([
                        $customer->email,
                        $customer->phone,
                    ]);

                    $results[] = [
                        'group' => 'Clientes',
                        'type' => 'customer',
                        'title' => $customer->name_customer ?: 'Cliente sin nombre',
                        'subtitle' => !empty($subtitleParts)
                            ? implode(' • ', $subtitleParts)
                            : 'Cliente registrado',
                        'meta' => 'Cliente',
                        'url' => route('customers.history', $customer->customer_id),
                    ];
                }
            } catch (\Throwable $e) {
                //
            }
        } */

        /*
        |--------------------------------------------------------------------------
        | Ventas
        |--------------------------------------------------------------------------
        */
        /* if (UserAccess::has($user, 'sales.view')) {
            try {
                $sales = DB::table('sales as s')
                    ->leftJoin('customers as c', 'c.customer_id', '=', 's.customer_id')
                    ->where('s.company_id', $companyId)
                    ->where(function ($query) use ($normalized) {
                        $query->whereRaw('LOWER(COALESCE(s.folio, "")) LIKE ?', ['%' . $normalized . '%'])
                            ->orWhereRaw('LOWER(COALESCE(c.name_customer, "")) LIKE ?', ['%' . $normalized . '%']);
                    })
                    ->orderByDesc('s.sale_id')
                    ->limit(8)
                    ->get([
                        's.sale_id',
                        's.folio',
                        's.total',
                        'c.name_customer',
                    ]);

                foreach ($sales as $sale) {
                    $results[] = [
                        'group' => 'Ventas',
                        'type' => 'sale',
                        'title' => $sale->folio ?: ('Venta #' . $sale->sale_id),
                        'subtitle' => $sale->name_customer
                            ? 'Cliente: ' . $sale->name_customer
                            : 'Venta registrada',
                        'meta' => 'Total: $' . number_format((float) $sale->total, 2),
                        'url' => route('sales.show', $sale->sale_id),
                    ];
                }
            } catch (\Throwable $e) {
                //
            }
        } */

        /*
        |--------------------------------------------------------------------------
        | Pagos
        |--------------------------------------------------------------------------
        */
        /* try {
            $payments = DB::table('payments as p')
                ->leftJoin('sales as s', 's.sale_id', '=', 'p.sale_id')
                ->where('p.company_id', $companyId)
                ->where(function ($query) use ($normalized) {
                    $query->whereRaw('LOWER(COALESCE(p.reference, "")) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(COALESCE(p.payment_method, "")) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(COALESCE(s.folio, "")) LIKE ?', ['%' . $normalized . '%']);
                })
                ->orderByDesc('p.payment_id')
                ->limit(6)
                ->get([
                    'p.payment_id',
                    'p.reference',
                    'p.amount',
                    'p.payment_method',
                    's.folio',
                ]);

            foreach ($payments as $payment) {
                $results[] = [
                    'group' => 'Pagos',
                    'type' => 'payment',
                    'title' => $payment->reference ?: ('Pago #' . $payment->payment_id),
                    'subtitle' => $payment->folio
                        ? 'Venta: ' . $payment->folio
                        : ($payment->payment_method ?: 'Pago registrado'),
                    'meta' => 'Monto: $' . number_format((float) $payment->amount, 2),
                    'url' => route('payments.show', $payment->payment_id),
                ];
            }
        } catch (\Throwable $e) {
            //
        }

        $results = collect($results)
            ->groupBy('group')
            ->map(function ($items, $group) {
                return [
                    'group' => $group,
                    'items' => array_values($items->take(8)->toArray()),
                ];
            })
            ->values()
            ->toArray();

        return response()->json([
            'query' => $term,
            'results' => $results,
        ]);
    }

    private function resolveCompanyId($user): ?int
    {
        if (!$user) {
            return null;
        }

        foreach (['company_id', 'id_company'] as $column) {
            if (isset($user->{$column}) && $user->{$column}) {
                return (int) $user->{$column};
            }
        }

        try {
            $companyId = DB::table('users')
                ->where('userr_id', $user->userr_id ?? $user->id ?? 0)
                ->value('company_id');

            return $companyId ? (int) $companyId : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
} */