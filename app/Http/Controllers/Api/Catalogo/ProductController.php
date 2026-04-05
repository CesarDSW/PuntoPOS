<?php

namespace App\Http\Controllers\Api\Catalogo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductController extends CatalogBaseController
{
    public function show(Request $request, int $id)
    {
        $companyId = $this->getCompanyId();
        $branchId = $this->resolveBranchId(
            $request->filled('branch_id') ? (int) $request->branch_id : null,
            $companyId
        );

        if (!$branchId) {
            throw ValidationException::withMessages([
                'branch_id' => ['Debes tener una sucursal seleccionada.'],
            ]);
        }

        $product = DB::table('productt as p')
            ->join('branch_product_stock as bps', function ($join) use ($branchId) {
                $join->on('bps.product_idfk', '=', 'p.product_id')
                     ->where('bps.branch_idfk', '=', $branchId);
            })
            ->leftJoin('category as c', 'c.category_id', '=', 'p.category_idfk')
            ->where('p.product_id', $id)
            ->where('p.company_idfk', $companyId)
            ->select([
                'p.product_id',
                'p.name_product',
                'p.code_product',
                'p.description_product',
                'p.price',
                'p.cost',
                'p.category_idfk',
                'c.name_category',
                'bps.status_stock as status_product',
                'bps.stocks as current_stock',
                'bps.minimum_stock as minimum_stock',
            ])
            ->first();

        if (!$product) {
            throw ValidationException::withMessages([
                'product_id' => ['El producto no existe en la sucursal actual o no pertenece a la empresa del usuario.'],
            ]);
        }

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $companyId = $this->getCompanyId();

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
            'name_product' => ['required', 'string', 'max:80'],
            'code_product' => ['required', 'string', 'max:15'],
            'category_id' => ['required', 'integer', 'exists:category,category_id'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'stock_initial' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'description_product' => ['nullable', 'string', 'max:250'],
            'status' => ['nullable'],
        ]);

        $branchId = $this->resolveBranchId(
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            $companyId
        );

        if (!$branchId) {
            throw ValidationException::withMessages([
                'branch_id' => ['Debes tener una sucursal seleccionada para crear productos.'],
            ]);
        }

        $this->getCategoryOrFail((int) $validated['category_id'], $companyId);

        $status = $this->normalizeStatusValue($validated['status'] ?? 1, true);
        $stockInitial = (int) ($validated['stock_initial'] ?? 0);
        $minimumStock = isset($validated['minimum_stock']) ? (int) $validated['minimum_stock'] : 0;
        $codeProduct = trim($validated['code_product']);

        $existingProduct = DB::table('productt')
            ->where('company_idfk', $companyId)
            ->where('code_product', $codeProduct)
            ->first();

        if ($existingProduct) {
            $existingBranchStock = DB::table('branch_product_stock')
                ->where('branch_idfk', $branchId)
                ->where('product_idfk', $existingProduct->product_id)
                ->first();

            if ($existingBranchStock && (int) $existingBranchStock->status_stock === 1) {
                throw ValidationException::withMessages([
                    'code_product' => ['Ese producto ya existe en la sucursal actual.'],
                ]);
            }

            DB::transaction(function () use (
                $existingProduct,
                $existingBranchStock,
                $branchId,
                $stockInitial,
                $minimumStock,
                $status
            ) {
                if ($existingBranchStock) {
                    DB::table('branch_product_stock')
                        ->where('branch_idfk', $branchId)
                        ->where('product_idfk', $existingProduct->product_id)
                        ->update([
                            'stocks' => $stockInitial > 0 ? $stockInitial : (int) $existingBranchStock->stocks,
                            'minimum_stock' => $minimumStock,
                            'status_stock' => $status,
                        ]);
                } else {
                    DB::table('branch_product_stock')->insert([
                        'branch_idfk' => $branchId,
                        'product_idfk' => $existingProduct->product_id,
                        'stocks' => $stockInitial,
                        'minimum_stock' => $minimumStock,
                        'status_stock' => $status,
                    ]);
                }

                $this->syncProductGlobalStatus((int) $existingProduct->product_id);
            });

            return response()->json([
                'message' => 'El producto ya existía en la empresa y fue asignado a la sucursal actual.',
                'product_id' => (int) $existingProduct->product_id,
                'reused_existing' => true,
            ]);
        }

        $result = DB::transaction(function () use ($validated, $companyId, $branchId, $status, $stockInitial, $minimumStock) {
            $productId = DB::table('productt')->insertGetId([
                'name_product' => trim($validated['name_product']),
                'code_product' => trim($validated['code_product']),
                'description_product' => $validated['description_product'] ?? null,
                'price' => $validated['price'],
                'cost' => $validated['cost'] ?? null,
                'status_product' => 1,
                'company_idfk' => $companyId,
                'category_idfk' => (int) $validated['category_id'],
            ]);

            DB::table('branch_product_stock')->insert([
                'branch_idfk' => $branchId,
                'product_idfk' => $productId,
                'stocks' => $stockInitial,
                'minimum_stock' => $minimumStock,
                'status_stock' => $status,
            ]);

            $this->syncProductGlobalStatus((int) $productId);

            return $productId;
        });

        return response()->json([
            'message' => 'Producto creado correctamente.',
            'product_id' => $result,
            'reused_existing' => false,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $companyId = $this->getCompanyId();

        $product = DB::table('productt')
            ->where('product_id', $id)
            ->where('company_idfk', $companyId)
            ->first();

        if (!$product) {
            throw ValidationException::withMessages([
                'product_id' => ['El producto no existe para la empresa del usuario.'],
            ]);
        }

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
            'name_product' => ['required', 'string', 'max:80'],
            'code_product' => [
                'required',
                'string',
                'max:15',
                Rule::unique('productt', 'code_product')
                    ->ignore($id, 'product_id')
                    ->where(function ($q) use ($companyId) {
                        return $q->where('company_idfk', $companyId);
                    }),
            ],
            'category_id' => ['required', 'integer', 'exists:category,category_id'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'description_product' => ['nullable', 'string', 'max:250'],
            'status' => ['nullable'],
        ]);

        $branchId = $this->resolveBranchId(
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            $companyId
        );

        if (!$branchId) {
            throw ValidationException::withMessages([
                'branch_id' => ['Debes tener una sucursal seleccionada para actualizar productos.'],
            ]);
        }

        $this->getCategoryOrFail((int) $validated['category_id'], $companyId);

        $status = $this->normalizeStatusValue($validated['status'] ?? 1, true);
        $minimumStock = isset($validated['minimum_stock']) ? (int) $validated['minimum_stock'] : 0;

        DB::transaction(function () use ($validated, $id, $branchId, $status, $minimumStock) {
            DB::table('productt')
                ->where('product_id', $id)
                ->update([
                    'name_product' => trim($validated['name_product']),
                    'code_product' => trim($validated['code_product']),
                    'description_product' => $validated['description_product'] ?? null,
                    'price' => $validated['price'],
                    'cost' => $validated['cost'] ?? null,
                    'category_idfk' => (int) $validated['category_id'],
                ]);

            $branchStock = DB::table('branch_product_stock')
                ->where('branch_idfk', $branchId)
                ->where('product_idfk', $id)
                ->first();

            if ($branchStock) {
                DB::table('branch_product_stock')
                    ->where('branch_idfk', $branchId)
                    ->where('product_idfk', $id)
                    ->update([
                        'minimum_stock' => $minimumStock,
                        'status_stock' => $status,
                    ]);
            } else {
                DB::table('branch_product_stock')->insert([
                    'branch_idfk' => $branchId,
                    'product_idfk' => $id,
                    'stocks' => 0,
                    'minimum_stock' => $minimumStock,
                    'status_stock' => $status,
                ]);
            }

            $this->syncProductGlobalStatus($id);
        });

        return response()->json([
            'message' => 'Producto actualizado correctamente.',
        ]);
    }

    public function deactivate(Request $request, int $id)
    {
        $companyId = $this->getCompanyId();
        $branchId = $this->resolveBranchId(
            $request->filled('branch_id') ? (int) $request->branch_id : null,
            $companyId
        );

        if (!$branchId) {
            throw ValidationException::withMessages([
                'branch_id' => ['Debes tener una sucursal seleccionada.'],
            ]);
        }

        $exists = DB::table('productt as p')
            ->join('branch_product_stock as bps', function ($join) use ($branchId) {
                $join->on('bps.product_idfk', '=', 'p.product_id')
                     ->where('bps.branch_idfk', '=', $branchId);
            })
            ->where('p.product_id', $id)
            ->where('p.company_idfk', $companyId)
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'product_id' => ['El producto no existe en la sucursal actual.'],
            ]);
        }

        DB::transaction(function () use ($id, $branchId) {
            DB::table('branch_product_stock')
                ->where('branch_idfk', $branchId)
                ->where('product_idfk', $id)
                ->update([
                    'status_stock' => 0,
                ]);

            $this->syncProductGlobalStatus($id);
        });

        return response()->json([
            'message' => 'Producto desactivado correctamente en la sucursal actual.',
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $this->getCompanyId();
        $branchId = $this->resolveBranchId(
            $request->filled('branch_id') ? (int) $request->branch_id : null,
            $companyId
        );

        if (!$branchId) {
            throw ValidationException::withMessages([
                'branch_id' => ['Debes tener una sucursal seleccionada.'],
            ]);
        }

        $branchStock = DB::table('productt as p')
            ->join('branch_product_stock as bps', function ($join) use ($branchId) {
                $join->on('bps.product_idfk', '=', 'p.product_id')
                     ->where('bps.branch_idfk', '=', $branchId);
            })
            ->where('p.product_id', $id)
            ->where('p.company_idfk', $companyId)
            ->select([
                'p.product_id',
                'bps.stocks',
            ])
            ->first();

        if (!$branchStock) {
            throw ValidationException::withMessages([
                'product_id' => ['El producto no existe en la sucursal actual.'],
            ]);
        }

        if ((int) $branchStock->stocks > 0) {
            throw ValidationException::withMessages([
                'product_id' => ['Solo puedes eliminar el producto de la sucursal actual cuando su stock en esa sucursal sea 0.'],
            ]);
        }

        $relationsCount = (int) DB::table('branch_product_stock')
            ->where('product_idfk', $id)
            ->count();

        $totalStock = (int) DB::table('branch_product_stock')
            ->where('product_idfk', $id)
            ->sum('stocks');

        try {
            DB::transaction(function () use ($id, $branchId, $companyId, $relationsCount, $totalStock) {
                if ($relationsCount > 1) {
                    DB::table('branch_product_stock')
                        ->where('branch_idfk', $branchId)
                        ->where('product_idfk', $id)
                        ->delete();

                    $this->syncProductGlobalStatus($id);
                    return;
                }

                if ($totalStock > 0) {
                    throw ValidationException::withMessages([
                        'product_id' => ['Solo puedes eliminar el producto cuando su stock total sea 0.'],
                    ]);
                }

                DB::table('branch_product_stock')
                    ->where('product_idfk', $id)
                    ->delete();

                DB::table('productt')
                    ->where('product_id', $id)
                    ->where('company_idfk', $companyId)
                    ->delete();
            });

            return response()->json([
                'message' => $relationsCount > 1
                    ? 'Producto eliminado de la sucursal actual correctamente.'
                    : 'Producto eliminado correctamente.',
            ]);
        } catch (Throwable $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            }

            return response()->json([
                'message' => 'No se puede eliminar el producto porque tiene movimientos o registros relacionados.',
            ], 422);
        }
    }
}