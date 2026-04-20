<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Support\CompanyPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryAdjustmentController extends Controller
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

    private function getProductOrFail(int $productId, int $companyId)
    {
        $product = DB::table('productt')
            ->where('product_id', $productId)
            ->where('company_idfk', $companyId)
            ->first();

        if (!$product) {
            throw ValidationException::withMessages([
                'product_id' => ['El producto no pertenece a la empresa del usuario.'],
            ]);
        }

        return $product;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id'  => ['nullable', 'integer', 'exists:branch,branch_id'],
            'product_id' => ['required', 'integer', 'exists:productt,product_id'],
            'type'       => ['required', 'string'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'reason'     => ['required', 'string', 'max:255'],
        ]);

        $authUser  = $this->getAuthenticatedUser();
        $companyId = (int) $authUser->company_idfk;
        $userId    = (int) $authUser->userr_id;

        $branchId = $this->resolveBranchId(
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            $companyId
        );

        if (!$branchId) {
            throw ValidationException::withMessages([
                'branch_id' => ['No hay una sucursal seleccionada ni registrada para esta empresa.'],
            ]);
        }

        $productId = (int) $validated['product_id'];
        $quantity  = (int) $validated['quantity'];
        $reason    = trim($validated['reason']);

        $typeInput = strtoupper(trim($validated['type']));

        $movementType = match ($typeInput) {
            'IN', 'ENTRADA'  => 'ENTRADA',
            'OUT', 'SALIDA'  => 'SALIDA',
            default          => null,
        };

        if (!$movementType) {
            throw ValidationException::withMessages([
                'type' => ['El tipo debe ser IN, OUT, ENTRADA o SALIDA.'],
            ]);
        }

        $result = DB::transaction(function () use (
            $companyId,
            $userId,
            $branchId,
            $productId,
            $quantity,
            $reason,
            $movementType
        ) {
            $user = DB::table('userr')
                ->where('userr_id', $userId)
                ->where('company_idfk', $companyId)
                ->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'userr_id' => ['El usuario autenticado no pertenece a la empresa indicada.'],
                ]);
            }

            $branch = $this->getBranchOrFail($branchId, $companyId);
            $product = $this->getProductOrFail($productId, $companyId);

            $stockRow = DB::table('branch_product_stock')
                ->where('branch_idfk', $branchId)
                ->where('product_idfk', $productId)
                ->lockForUpdate()
                ->first();

            if (!$stockRow) {
                throw ValidationException::withMessages([
                    'product_id' => ['No existe stock registrado para este producto en la sucursal indicada.'],
                ]);
            }

            $currentStock = (int) $stockRow->stocks;
            $minimumStock = (int) $stockRow->minimum_stock;

            if ($movementType === 'ENTRADA') {
                $newStock = $currentStock + $quantity;
            } else {
                $newStock = $currentStock - $quantity;

                if ($newStock < 0) {
                    throw ValidationException::withMessages([
                        'quantity' => ['La cantidad a sacar es mayor al stock disponible. Operación cancelada.'],
                    ]);
                }
            }

            $movementId = DB::table('inventory_movement')->insertGetId([
                'date_time'               => now(),
                'type_invmov'             => $movementType,
                'reason_invmov'           => $reason,
                'company_idfk'            => $companyId,
                'origin_branch_idfk'      => $branchId,
                'destination_branch_idfk' => null,
                'userr_idfk'              => $userId,
            ]);

            DB::table('inventory_movement_item')->insert([
                'invmov_idfk'    => $movementId,
                'product_idfk'   => $productId,
                'amount'         => $quantity,
                'previous_stock' => $currentStock,
                'new_stock'      => $newStock,
            ]);

            DB::table('branch_product_stock')
                ->where('branch_idfk', $branchId)
                ->where('product_idfk', $productId)
                ->update([
                    'stocks' => $newStock,
                ]);
            
            app(NotificationService::class)->handleStockChanged(
                companyId: $companyId,
                branchId: $branchId,
                productId: $productId,
                productName: (string) $product->name_product,
                oldStock: $currentStock,
                newStock: $newStock,
                minimumStock: $minimumStock
            );

            return [
                'message' => 'Ajuste de inventario realizado correctamente.',
                'data' => [
                    'movement_id'    => $movementId,
                    'date_time'      => now()->toDateTimeString(),
                    'date_time_display' => CompanyPreference::formatDateTimeForCompany($companyId, now()),
                    'type'           => $movementType,
                    'reason'         => $reason,
                    'branch_id'      => $branchId,
                    'branch_name'    => $branch->name_branch,
                    'product_id'     => $productId,
                    'product_name'   => $product->name_product,
                    'product_code'   => $product->code_product,
                    'quantity'       => $quantity,
                    'previous_stock' => $currentStock,
                    'new_stock'      => $newStock,
                    'minimum_stock'  => $minimumStock,
                    'userr_id'       => $userId,
                    'user_name'      => $user->name_user,
                ],
            ];
        });

        return response()->json($result, 201);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
            'type'      => ['nullable', 'string'],
            'search'    => ['nullable', 'string', 'max:100'],
            'from'      => ['nullable', 'date'],
            'to'        => ['nullable', 'date'],
            'page'      => ['nullable', 'integer', 'min:1'],
            'per_page'  => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $authUser  = $this->getAuthenticatedUser();
        $companyId = (int) $authUser->company_idfk;

        $branchId = isset($validated['branch_id']) ? (int) $validated['branch_id'] : null;
        $search   = $validated['search'] ?? null;
        $from     = $validated['from'] ?? null;
        $to       = $validated['to'] ?? null;
        $perPage  = (int) ($validated['per_page'] ?? 20);

        if ($branchId) {
            $this->getBranchOrFail($branchId, $companyId);
        }

        $typeInput = isset($validated['type']) ? strtoupper(trim($validated['type'])) : null;

        $movementType = match ($typeInput) {
            'IN', 'ENTRADA'             => 'ENTRADA',
            'OUT', 'SALIDA'             => 'SALIDA',
            'AJUSTE'                    => 'AJUSTE',
            'TRANSFER', 'TRANSFERENCIA' => 'TRANSFERENCIA',
            null, ''                    => null,
            default                     => null,
        };

        if ($typeInput && !$movementType) {
            throw ValidationException::withMessages([
                'type' => ['El tipo debe ser ENTRADA, SALIDA, AJUSTE o TRANSFERENCIA.'],
            ]);
        }

        $query = DB::table('inventory_movement_item as imi')
            ->join('inventory_movement as im', 'im.invmov_id', '=', 'imi.invmov_idfk')
            ->join('productt as p', 'p.product_id', '=', 'imi.product_idfk')
            ->leftJoin('branch as ob', 'ob.branch_id', '=', 'im.origin_branch_idfk')
            ->leftJoin('branch as db', 'db.branch_id', '=', 'im.destination_branch_idfk')
            ->join('userr as u', 'u.userr_id', '=', 'im.userr_idfk')
            ->where('im.company_idfk', $companyId)
            ->select([
                'im.invmov_id as movement_id',
                'im.date_time',
                'im.type_invmov as type',
                'im.reason_invmov as reason',
                'im.origin_branch_idfk as origin_branch_id',
                'ob.name_branch as origin_branch_name',
                'im.destination_branch_idfk as destination_branch_id',
                'db.name_branch as destination_branch_name',
                'im.userr_idfk as userr_id',
                'u.name_user as user_name',
                'p.product_id',
                'p.name_product as product_name',
                'p.code_product as product_code',
                'imi.amount as quantity',
                'imi.previous_stock',
                'imi.new_stock',
                DB::raw("
                    CASE
                        WHEN im.type_invmov = 'SALIDA' THEN CONCAT('-', imi.amount)
                        ELSE CONCAT('+', imi.amount)
                    END as signed_quantity
                "),
            ]);

        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('im.origin_branch_idfk', $branchId)
                  ->orWhere('im.destination_branch_idfk', $branchId);
            });
        }

        if ($movementType) {
            $query->where('im.type_invmov', $movementType);
        }

        if ($from) {
            $query->whereDate('im.date_time', '>=', $from);
        }

        if ($to) {
            $query->whereDate('im.date_time', '<=', $to);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('im.reason_invmov', 'like', "%{$search}%")
                  ->orWhere('p.name_product', 'like', "%{$search}%")
                  ->orWhere('p.code_product', 'like', "%{$search}%")
                  ->orWhere('u.name_user', 'like', "%{$search}%");
            });
        }

        $history->getCollection()->transform(function ($row) use ($companyId) {
            $row->date_time_display = CompanyPreference::formatDateTimeForCompany($companyId, $row->date_time);
            return $row;
        });

        return response()->json($history);
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'branch_id'          => ['nullable', 'integer', 'exists:branch,branch_id'],
            'type'               => ['required', 'string'],
            'reason'             => ['required', 'string', 'max:255'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:productt,product_id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
        ]);

        $authUser  = $this->getAuthenticatedUser();
        $companyId = (int) $authUser->company_idfk;
        $userId    = (int) $authUser->userr_id;

        $branchId = $this->resolveBranchId(
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            $companyId
        );

        if (!$branchId) {
            throw ValidationException::withMessages([
                'branch_id' => ['No hay una sucursal seleccionada ni registrada para esta empresa.'],
            ]);
        }

        $reason = trim($validated['reason']);
        $items  = $validated['items'];

        $typeInput = strtoupper(trim($validated['type']));

        $movementType = match ($typeInput) {
            'IN', 'ENTRADA'   => 'ENTRADA',
            'OUT', 'SALIDA'   => 'SALIDA',
            'AJUSTE'          => 'AJUSTE',
            default           => null,
        };

        if (!$movementType) {
            throw ValidationException::withMessages([
                'type' => ['El tipo debe ser IN, OUT, ENTRADA, SALIDA o AJUSTE.'],
            ]);
        }

        $result = DB::transaction(function () use (
            $companyId,
            $userId,
            $branchId,
            $reason,
            $items,
            $movementType
        ) {
            $user = DB::table('userr')
                ->where('userr_id', $userId)
                ->where('company_idfk', $companyId)
                ->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'userr_id' => ['El usuario autenticado no pertenece a la empresa indicada.'],
                ]);
            }

            $branch = $this->getBranchOrFail($branchId, $companyId);

            $productIds = collect($items)->pluck('product_id')->all();
            $duplicates = collect($productIds)->duplicates()->values()->all();

            if (!empty($duplicates)) {
                throw ValidationException::withMessages([
                    'items' => ['No puedes repetir productos dentro del mismo ajuste masivo.'],
                ]);
            }

            $products = DB::table('productt')
                ->where('company_idfk', $companyId)
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');

            foreach ($items as $index => $item) {
                if (!isset($products[$item['product_id']])) {
                    throw ValidationException::withMessages([
                        "items.$index.product_id" => ['El producto no pertenece a la empresa del usuario.'],
                    ]);
                }
            }

            $stockRows = DB::table('branch_product_stock')
                ->where('branch_idfk', $branchId)
                ->whereIn('product_idfk', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_idfk');

            foreach ($items as $index => $item) {
                if (!isset($stockRows[$item['product_id']])) {
                    throw ValidationException::withMessages([
                        "items.$index.product_id" => ['No existe stock registrado para ese producto en la sucursal indicada.'],
                    ]);
                }
            }

            $processedItems = [];

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $quantity  = (int) $item['quantity'];

                $stockRow = $stockRows[$productId];
                $currentStock = (int) $stockRow->stocks;
                $minimumStock = (int) $stockRow->minimum_stock;

                if ($movementType === 'SALIDA') {
                    $newStock = $currentStock - $quantity;

                    if ($newStock < 0) {
                        throw ValidationException::withMessages([
                            'items' => [
                                "La cantidad a sacar del producto ID {$productId} es mayor al stock disponible. Operación cancelada."
                            ],
                        ]);
                    }
                } else {
                    $newStock = $currentStock + $quantity;
                }

                $processedItems[] = [
                    'product_id'     => $productId,
                    'product_name'   => $products[$productId]->name_product,
                    'product_code'   => $products[$productId]->code_product,
                    'quantity'       => $quantity,
                    'previous_stock' => $currentStock,
                    'new_stock'      => $newStock,
                    'minimum_stock'  => $minimumStock,
                ];
            }

            $movementId = DB::table('inventory_movement')->insertGetId([
                'date_time'               => now(),
                'type_invmov'             => $movementType,
                'reason_invmov'           => $reason,
                'company_idfk'            => $companyId,
                'origin_branch_idfk'      => $branchId,
                'destination_branch_idfk' => null,
                'userr_idfk'              => $userId,
            ]);

            foreach ($processedItems as $item) {
                DB::table('inventory_movement_item')->insert([
                    'invmov_idfk'    => $movementId,
                    'product_idfk'   => $item['product_id'],
                    'amount'         => $item['quantity'],
                    'previous_stock' => $item['previous_stock'],
                    'new_stock'      => $item['new_stock'],
                ]);

                DB::table('branch_product_stock')
                    ->where('branch_idfk', $branchId)
                    ->where('product_idfk', $item['product_id'])
                    ->update([
                        'stocks' => $item['new_stock'],
                    ]);
                
                app(NotificationService::class)->handleStockChanged(
                    companyId: $companyId,
                    branchId: $branchId,
                    productId: (int) $item['product_id'],
                    productName: (string) $item['product_name'],
                    oldStock: (int) $item['previous_stock'],
                    newStock: (int) $item['new_stock'],
                    minimumStock: (int) $item['minimum_stock']
                );
            }

            return [
                'message' => 'Ajuste masivo realizado correctamente.',
                'data' => [
                    'movement_id' => $movementId,
                    'date_time'   => now()->toDateTimeString(),
                    'type'        => $movementType,
                    'reason'      => $reason,
                    'branch_id'   => $branchId,
                    'branch_name' => $branch->name_branch,
                    'userr_id'    => $userId,
                    'user_name'   => $user->name_user,
                    'items_count' => count($processedItems),
                    'items'       => $processedItems,
                ],
            ];
        });

        return response()->json($result, 201);
    }
}