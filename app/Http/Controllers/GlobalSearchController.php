<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\UserAccess;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
                'results' => [],
            ], 401);
        }

        $term = trim((string) $request->get('q', ''));

        if ($term === '') {
            return response()->json([
                'success' => true,
                'results' => [],
            ]);
        }

        $companyId = (int) ($user->company_idfk ?? 0);
        $termLike = '%' . $term . '%';

        $results = collect();

        /*
        |--------------------------------------------------------------------------
        | CLIENTES
        |--------------------------------------------------------------------------
        */
        try {
            if (UserAccess::has($user, 'customers.view') || UserAccess::has($user, 'sales.pos.use')) {
                $customers = DB::table('customers')
                    ->select([
                        'customer_id',
                        'name_customer',
                        'email',
                        'phone',
                    ])
                    ->where('company_idfk', $companyId)
                    ->where(function ($query) use ($termLike) {
                        $query->where('name_customer', 'like', $termLike)
                            ->orWhere('email', 'like', $termLike)
                            ->orWhere('phone', 'like', $termLike);
                    })
                    ->limit(5)
                    ->get();

                foreach ($customers as $customer) {
                    $results->push([
                        'type' => 'Cliente',
                        'title' => $customer->name_customer ?: 'Cliente sin nombre',
                        'subtitle' => collect([
                            $customer->email,
                            $customer->phone,
                        ])->filter()->implode(' • '),
                        'url' => route('customers'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Evita romper toda la búsqueda si este bloque falla
        }

        /*
        |--------------------------------------------------------------------------
        | PRODUCTOS
        |--------------------------------------------------------------------------
        */
        try {
            if (UserAccess::has($user, 'catalog.view') || UserAccess::has($user, 'inventory.view') || UserAccess::has($user, 'sales.pos.use')) {
                $products = DB::table('products')
                    ->select([
                        'product_id',
                        'name_product',
                        'sku',
                        'barcode',
                    ])
                    ->where('company_idfk', $companyId)
                    ->where(function ($query) use ($termLike) {
                        $query->where('name_product', 'like', $termLike)
                            ->orWhere('sku', 'like', $termLike)
                            ->orWhere('barcode', 'like', $termLike);
                    })
                    ->limit(6)
                    ->get();

                foreach ($products as $product) {
                    $results->push([
                        'type' => 'Producto',
                        'title' => $product->name_product ?: 'Producto sin nombre',
                        'subtitle' => collect([
                            $product->sku ? 'SKU: ' . $product->sku : null,
                            $product->barcode ? 'Código: ' . $product->barcode : null,
                        ])->filter()->implode(' • '),
                        'url' => route('catalog.index'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            //
        }

        /*
        |--------------------------------------------------------------------------
        | SERVICIOS
        |--------------------------------------------------------------------------
        */
        try {
            if (UserAccess::has($user, 'catalog.view')) {
                $services = DB::table('services')
                    ->select([
                        'service_id',
                        'name_service',
                        'description',
                    ])
                    ->where('company_idfk', $companyId)
                    ->where(function ($query) use ($termLike) {
                        $query->where('name_service', 'like', $termLike)
                            ->orWhere('description', 'like', $termLike);
                    })
                    ->limit(5)
                    ->get();

                foreach ($services as $service) {
                    $results->push([
                        'type' => 'Servicio',
                        'title' => $service->name_service ?: 'Servicio sin nombre',
                        'subtitle' => $service->description ?: 'Servicio del catálogo',
                        'url' => route('catalog.index'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            //
        }

        /*
        |--------------------------------------------------------------------------
        | CATEGORÍAS
        |--------------------------------------------------------------------------
        */
        try {
            if (UserAccess::has($user, 'catalog.view')) {
                $categories = DB::table('categories')
                    ->select([
                        'category_id',
                        'name_category',
                        'description',
                    ])
                    ->where('company_idfk', $companyId)
                    ->where(function ($query) use ($termLike) {
                        $query->where('name_category', 'like', $termLike)
                            ->orWhere('description', 'like', $termLike);
                    })
                    ->limit(5)
                    ->get();

                foreach ($categories as $category) {
                    $results->push([
                        'type' => 'Categoría',
                        'title' => $category->name_category ?: 'Categoría sin nombre',
                        'subtitle' => $category->description ?: 'Categoría del catálogo',
                        'url' => route('catalog.index'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            //
        }

        /*
        |--------------------------------------------------------------------------
        | VENTAS
        |--------------------------------------------------------------------------
        */
        try {
            if (UserAccess::has($user, 'sales.view')) {
                $sales = DB::table('sales as s')
                    ->leftJoin('customers as c', 'c.customer_id', '=', 's.customer_idfk')
                    ->select([
                        's.sale_id',
                        's.total',
                        's.date_time',
                        'c.name_customer',
                    ])
                    ->where('s.company_idfk', $companyId)
                    ->where(function ($query) use ($termLike) {
                        $query->where('s.sale_id', 'like', $termLike)
                            ->orWhere('c.name_customer', 'like', $termLike);
                    })
                    ->orderByDesc('s.sale_id')
                    ->limit(5)
                    ->get();

                foreach ($sales as $sale) {
                    $results->push([
                        'type' => 'Venta',
                        'title' => 'Venta #' . $sale->sale_id,
                        'subtitle' => collect([
                            $sale->name_customer ? 'Cliente: ' . $sale->name_customer : null,
                            isset($sale->total) ? 'Total: $' . number_format((float) $sale->total, 2) : null,
                        ])->filter()->implode(' • '),
                        'url' => route('sales.show', ['id' => $sale->sale_id]),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            //
        }

        /*
        |--------------------------------------------------------------------------
        | PAGOS
        |--------------------------------------------------------------------------
        */
        try {
            $paymentsTableExists = $this->tableExists('payments');

            if ($paymentsTableExists) {
                $payments = DB::table('payments')
                    ->select([
                        'payment_id',
                        'payment_method',
                        'status_payment',
                        'amount',
                    ])
                    ->where('company_idfk', $companyId)
                    ->where(function ($query) use ($termLike) {
                        $query->where('payment_method', 'like', $termLike)
                            ->orWhere('status_payment', 'like', $termLike)
                            ->orWhere('payment_id', 'like', $termLike);
                    })
                    ->limit(5)
                    ->get();

                foreach ($payments as $payment) {
                    $results->push([
                        'type' => 'Pago',
                        'title' => 'Pago #' . $payment->payment_id,
                        'subtitle' => collect([
                            $payment->payment_method,
                            $payment->status_payment,
                            isset($payment->amount) ? '$' . number_format((float) $payment->amount, 2) : null,
                        ])->filter()->implode(' • '),
                        'url' => route('payments.show', ['id' => $payment->payment_id]),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            //
        }

        /*
        |--------------------------------------------------------------------------
        | SUCURSALES
        |--------------------------------------------------------------------------
        */
        try {
            $branchesTableExists = $this->tableExists('branches');

            if ($branchesTableExists) {
                $branches = DB::table('branches')
                    ->select([
                        'branch_id',
                        'name_branch',
                        'city',
                        'state',
                    ])
                    ->where('company_idfk', $companyId)
                    ->where(function ($query) use ($termLike) {
                        $query->where('name_branch', 'like', $termLike)
                            ->orWhere('city', 'like', $termLike)
                            ->orWhere('state', 'like', $termLike);
                    })
                    ->limit(5)
                    ->get();

                foreach ($branches as $branch) {
                    $results->push([
                        'type' => 'Sucursal',
                        'title' => $branch->name_branch ?: 'Sucursal',
                        'subtitle' => collect([
                            $branch->city,
                            $branch->state,
                        ])->filter()->implode(', '),
                        'url' => route('settings'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            //
        }

        /*
        |--------------------------------------------------------------------------
        | USUARIOS
        |--------------------------------------------------------------------------
        */
        try {
            if (UserAccess::has($user, 'settings.view') || UserAccess::has($user, 'users.manage')) {
                $users = DB::table('users')
                    ->select([
                        'userr_id',
                        'name_user',
                        'email',
                    ])
                    ->where('company_idfk', $companyId)
                    ->where(function ($query) use ($termLike) {
                        $query->where('name_user', 'like', $termLike)
                            ->orWhere('email', 'like', $termLike);
                    })
                    ->limit(5)
                    ->get();

                foreach ($users as $item) {
                    $results->push([
                        'type' => 'Usuario',
                        'title' => $item->name_user ?: 'Usuario',
                        'subtitle' => $item->email ?: 'Usuario del sistema',
                        'url' => route('settings'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            //
        }

        return response()->json([
            'success' => true,
            'results' => $results->take(20)->values(),
        ]);
    }

    private function tableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }
}