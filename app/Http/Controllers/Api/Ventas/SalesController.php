<?php

namespace App\Http\Controllers\Api\Ventas;

use App\Services\NotificationService;
use App\Support\CompanyPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class SalesController extends SalesBaseController
{
    public function summary(Request $request)
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

        $today = now()->toDateString();
        $last24 = now()->subDay();

        $todaySales = DB::table('sale')
            ->where('company_idfk', $companyId)
            ->where('branch_idfk', $branchId)
            ->whereDate('date_time', $today);

        $totalSoldToday = (float) (clone $todaySales)->sum('total');
        $avgTicket = (float) ((clone $todaySales)->avg('total') ?? 0);

        $salesLast24 = (int) DB::table('sale')
            ->where('company_idfk', $companyId)
            ->where('branch_idfk', $branchId)
            ->where('date_time', '>=', $last24)
            ->count();

        return response()->json([
            'total_sold_today' => $totalSoldToday,
            'total_sold_today_display' => CompanyPreference::formatMoneyForCompany($companyId, $totalSoldToday),
            'sales_last_24h' => $salesLast24,
            'avg_ticket_today' => $avgTicket,
            'avg_ticket_today_display' => CompanyPreference::formatMoneyForCompany($companyId, $avgTicket),
        ]);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
            'search' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:all,PAGADA,PENDIENTE,CANCELADA'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
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
        $date = $validated['date'] ?? null;
        $status = $validated['status'] ?? 'all';
        $perPage = (int) ($validated['per_page'] ?? 15);

        $itemsSub = DB::table('saleitem')
            ->select('sale_idfk', DB::raw('COUNT(*) as items_count'))
            ->groupBy('sale_idfk');

        $query = DB::table('sale as s')
            ->join('customer as c', 'c.customer_id', '=', 's.customer_idfk')
            ->leftJoin('payments as p', 'p.sale_idfk', '=', 's.sale_id')
            ->leftJoinSub($itemsSub, 'si', function ($join) {
                $join->on('si.sale_idfk', '=', 's.sale_id');
            })
            ->where('s.company_idfk', $companyId)
            ->where('s.branch_idfk', $branchId)
            ->select([
                's.sale_id',
                's.date_time',
                'c.name_customer',
                's.total',
                's.status_sale',
                DB::raw('COALESCE(si.items_count, 0) as items_count'),
                'p.payment_method',
            ]);

        if ($date) {
            $query->whereDate('s.date_time', $date);
        }

        if ($status !== 'all') {
            $query->where('s.status_sale', $status);
        }

        if ($search !== '') {
            $digits = preg_replace('/\D/', '', $search);

            $query->where(function ($q) use ($search, $digits) {
                $q->where('c.name_customer', 'like', "%{$search}%");

                if ($digits !== '') {
                    $q->orWhere('s.sale_id', (int) $digits)
                      ->orWhereRaw("CONCAT('V-', LPAD(s.sale_id, 5, '0')) LIKE ?", ["%{$search}%"]);
                }
            });
        }

        $sales = $query
            ->orderByDesc('s.date_time')
            ->paginate($perPage);

        $sales->getCollection()->transform(function ($row) use ($companyId) {
            return [
                'sale_id' => (int) $row->sale_id,
                'sale_folio' => $this->formatSaleFolio((int) $row->sale_id),
                'date_time' => $row->date_time,
                'date_time_display' => CompanyPreference::formatDateTimeForCompany($companyId, $row->date_time),
                'customer_name' => $row->name_customer,
                'items_count' => (int) $row->items_count,
                'total' => (float) $row->total,
                'total_display' => CompanyPreference::formatMoneyForCompany($companyId, $row->total),
                'payment_method' => $row->payment_method,
                'status_sale' => $row->status_sale,
            ];
        });

        return response()->json($sales);
    }

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

        $sale = DB::table('sale as s')
            ->join('customer as c', 'c.customer_id', '=', 's.customer_idfk')
            ->join('branch as b', 'b.branch_id', '=', 's.branch_idfk')
            ->join('userr as u', 'u.userr_id', '=', 's.cashier_userr_idfk')
            ->leftJoin('payments as p', 'p.sale_idfk', '=', 's.sale_id')
            ->where('s.sale_id', $id)
            ->where('s.company_idfk', $companyId)
            ->where('s.branch_idfk', $branchId)
            ->select([
                's.sale_id',
                's.date_time',
                's.subtotal',
                's.discount',
                's.total',
                's.status_sale',
                'c.customer_id',
                'c.name_customer',
                'c.phone',
                'c.email',
                'b.branch_id',
                'b.name_branch',
                'u.userr_id',
                'u.name_user',
                'p.payment_method',
                'p.status_payment',
                'p.amount_paid',
                'p.change_given',
                'p.reference_payment',
            ])
            ->first();

        if (!$sale) {
            throw ValidationException::withMessages([
                'sale_id' => ['La venta no existe en la sucursal actual.'],
            ]);
        }

        $items = DB::table('saleitem as si')
            ->leftJoin('productt as p', 'p.product_id', '=', 'si.product_idfk')
            ->leftJoin('servicee as sv', 'sv.service_id', '=', 'si.service_idfk')
            ->where('si.sale_idfk', $id)
            ->select([
                'si.saleitem_id',
                'si.item_type',
                'si.product_idfk',
                'si.service_idfk',
                'si.amount',
                'si.unit_price',
                'si.discount',
                'si.total_line',
                DB::raw("
                    CASE
                        WHEN si.item_type = 'PRODUCTO' THEN p.name_product
                        ELSE sv.name_service
                    END as item_name
                "),
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'saleitem_id' => (int) $item->saleitem_id,
                    'item_type' => $item->item_type,
                    'item_id' => $item->item_type === 'PRODUCTO'
                        ? (int) $item->product_idfk
                        : (int) $item->service_idfk,
                    'item_name' => $item->item_name,
                    'amount' => (int) $item->amount,
                    'unit_price' => (float) $item->unit_price,
                    'discount' => (float) $item->discount,
                    'total_line' => (float) $item->total_line,
                ];
            });

        $iva = round(((float) $sale->subtotal - (float) $sale->discount) * self::TAX_RATE, 2);

        return response()->json([
            'sale_id' => (int) $sale->sale_id,
            'sale_folio' => $this->formatSaleFolio((int) $sale->sale_id),
            'date_time' => $sale->date_time,
            'date_time_display' => CompanyPreference::formatDateTimeForCompany($companyId, $sale->date_time),
            'subtotal' => (float) $sale->subtotal,
            'subtotal_display' => CompanyPreference::formatMoneyForCompany($companyId, $sale->subtotal),
            'discount' => (float) $sale->discount,
            'discount_display' => CompanyPreference::formatMoneyForCompany($companyId, $sale->discount),
            'iva' => $iva,
            'iva_display' => CompanyPreference::formatMoneyForCompany($companyId, $iva),
            'total' => (float) $sale->total,
            'total_display' => CompanyPreference::formatMoneyForCompany($companyId, $sale->total),
            'status_sale' => $sale->status_sale,
            'customer' => [
                'customer_id' => (int) $sale->customer_id,
                'name_customer' => $sale->name_customer,
                'phone' => $sale->phone,
                'email' => $sale->email,
            ],
            'branch' => [
                'branch_id' => (int) $sale->branch_id,
                'name_branch' => $sale->name_branch,
            ],
            'cashier' => [
                'userr_id' => (int) $sale->userr_id,
                'name_user' => $sale->name_user,
            ],
            'payment' => [
                'payment_method' => $sale->payment_method,
                'status_payment' => $sale->status_payment,
                'amount_paid' => (float) ($sale->amount_paid ?? 0),
                'amount_paid_display' => CompanyPreference::formatMoneyForCompany($companyId, $sale->amount_paid ?? 0),
                'change_given' => (float) ($sale->change_given ?? 0),
                'change_given_display' => CompanyPreference::formatMoneyForCompany($companyId, $sale->change_given ?? 0),
                'reference_payment' => $sale->reference_payment,
            ],
            'items' => $items->map(function ($item) use ($companyId){
                return [
                    'saleitem_id' => $item['saleitem_id'],
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'item_name' => $item['item_name'],
                    'amount' => $item['amount'],
                    'unit_price' => $item['unit_price'],
                    'unit_price_display' => CompanyPreference::formatMoneyForCompany($companyId, $item['unit_price']),
                    'discount' => $item['discount'],
                    'discount_display' => CompanyPreference::formatMoneyForCompany($companyId, $item['discount']),
                    'total_line' => $item['total_line'],
                    'total_line_display' => CompanyPreference::formatMoneyForCompany($companyId, $item['total_line']),
                ];
            })->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
            'customer_id' => ['nullable', 'integer', 'exists:customer,customer_id'],
            'payment_method' => ['required', 'string'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'reference_payment' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:productt,product_id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $companyId = $this->getCompanyId();
        $userId = $this->getUserId();

        $branchId = $this->resolveBranchId(
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            $companyId
        );

        if (!$branchId) {
            throw ValidationException::withMessages([
                'branch_id' => ['Debes tener una sucursal seleccionada.'],
            ]);
        }

        $this->getBranchOrFail($branchId, $companyId);

        // Caja única por empresa
        $this->ensureOpenCashSessionOrFail($companyId);

        // Turno del usuario actual
        $this->ensureOpenShiftOrFail($companyId, $branchId, $userId);

        $paymentMethod = $this->normalizePaymentMethod($validated['payment_method']);

        if (isset($validated['customer_id'])) {
            $customer = $this->getCustomerOrFail((int) $validated['customer_id'], $companyId);
        } else {
            $customer = $this->getOrCreateGenericCustomer($companyId);
        }

        $items = $validated['items'];

        $productIds = collect($items)->pluck('product_id')->all();
        $duplicates = collect($productIds)->duplicates()->values()->all();

        if (!empty($duplicates)) {
            throw ValidationException::withMessages([
                'items' => ['No puedes repetir productos en la misma venta.'],
            ]);
        }

        try {
            $result = DB::transaction(function () use (
                $items,
                $productIds,
                $companyId,
                $branchId,
                $userId,
                $customer,
                $paymentMethod,
                $validated
            ) {
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

                $processedItems = [];
                $subtotal = 0.00;

                foreach ($items as $index => $item) {
                    $productId = (int) $item['product_id'];
                    $quantity = (int) $item['quantity'];

                    if (!isset($stockRows[$productId])) {
                        throw ValidationException::withMessages([
                            "items.$index.product_id" => ['No existe stock registrado para ese producto en la sucursal actual.'],
                        ]);
                    }

                    $stockRow = $stockRows[$productId];
                    $product = $products[$productId];

                    if ((int) $stockRow->status_stock !== 1 || (int) $product->status_product !== 1) {
                        throw ValidationException::withMessages([
                            "items.$index.product_id" => ['El producto no está activo para venta.'],
                        ]);
                    }

                    $currentStock = (int) $stockRow->stocks;
                    $newStock = $currentStock - $quantity;

                    if ($newStock < 0) {
                        throw ValidationException::withMessages([
                            "items.$index.quantity" => ['La cantidad solicitada supera el stock disponible.'],
                        ]);
                    }

                    $lineSubtotal = round((float) $product->price * $quantity, 2);
                    $subtotal += $lineSubtotal;

                    $processedItems[] = [
                        'product_id' => $productId,
                        'product_name' => $product->name_product,
                        'quantity' => $quantity,
                        'unit_price' => (float) $product->price,
                        'line_subtotal' => $lineSubtotal,
                        'previous_stock' => $currentStock,
                        'new_stock' => $newStock,
                        'minimum_stock' => (int) $stockRow->minimum_stock,
                    ];
                }

                $discount = 0.00;
                $iva = round(($subtotal - $discount) * self::TAX_RATE, 2);
                $total = round(($subtotal - $discount) + $iva, 2);

                if ($paymentMethod === 'EFECTIVO') {
                    $amountPaid = round((float) ($validated['amount_paid'] ?? 0), 2);

                    if ($amountPaid < $total) {
                        throw ValidationException::withMessages([
                            'amount_paid' => ['El monto recibido es menor al total de la venta.'],
                        ]);
                    }

                    $changeGiven = round($amountPaid - $total, 2);
                } else {
                    $amountPaid = $total;
                    $changeGiven = 0.00;
                }

                $saleId = DB::table('sale')->insertGetId([
                    'date_time' => now(),
                    'company_idfk' => $companyId,
                    'branch_idfk' => $branchId,
                    'cashier_userr_idfk' => $userId,
                    'customer_idfk' => $customer->customer_id,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $total,
                    'status_sale' => 'PAGADA',
                ]);

                foreach ($processedItems as $item) {
                    DB::table('saleitem')->insert([
                        'sale_idfk' => $saleId,
                        'item_type' => 'PRODUCTO',
                        'product_idfk' => $item['product_id'],
                        'service_idfk' => null,
                        'amount' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => 0,
                        'total_line' => $item['line_subtotal'],
                    ]);

                    DB::table('branch_product_stock')
                        ->where('branch_idfk', $branchId)
                        ->where('product_idfk', $item['product_id'])
                        ->update([
                            'stocks' => $item['new_stock'],
                        ]);
                }

                DB::table('payments')->insert([
                    'date_time' => now(),
                    'payment_method' => $paymentMethod,
                    'status_payment' => 'PAGADO',
                    'commission' => 0,
                    'amount_paid' => $amountPaid,
                    'change_given' => $changeGiven,
                    'reference_payment' => $validated['reference_payment'] ?? null,
                    'sale_idfk' => $saleId,
                    'customer_idfk' => $customer->customer_id,
                ]);

                $movementId = DB::table('inventory_movement')->insertGetId([
                    'date_time' => now(),
                    'type_invmov' => 'SALIDA',
                    'reason_invmov' => 'Venta ' . $this->formatSaleFolio($saleId),
                    'company_idfk' => $companyId,
                    'origin_branch_idfk' => $branchId,
                    'destination_branch_idfk' => null,
                    'userr_idfk' => $userId,
                ]);

                foreach ($processedItems as $item) {
                    DB::table('inventory_movement_item')->insert([
                        'invmov_idfk' => $movementId,
                        'product_idfk' => $item['product_id'],
                        'amount' => $item['quantity'],
                        'previous_stock' => $item['previous_stock'],
                        'new_stock' => $item['new_stock'],
                    ]);

                    app(Notificationervice::class)->handleStockChanged(
                        companyId: $companyId,
                        branchId: $branchId,
                        productId: (int) $item['product_id'],
                        productName: (string) $item['product_name'],
                        oldStock: (int) $item['previous_stock'],
                        newStock: (int) $item['new_stock'],
                        minimumStock: (int) $item['minimum_stock'],
                    );
                }

                return [
                    'sale_id' => $saleId,
                    'sale_folio' => $this->formatSaleFolio($saleId),
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'iva' => $iva,
                    'total' => $total,
                    'payment_method' => $paymentMethod,
                    'amount_paid' => $amountPaid,
                    'change_given' => $changeGiven,
                    'items_count' => count($processedItems),
                    'items' => $processedItems,
                ];
            });

            $result['date_time_display'] = CompanyPreference::formatDateTimeForCompany($companyId, $result['data']['data_time'] ?? now());
            $result['subtotal_display'] = CompanyPreference::formatMoneyForCompany($companyId, $result['data']['subtotal'] ?? 0);
            $result['discount_display'] = CompanyPreference::formatMoneyForCompany($companyId, $result['data']['discount'] ?? 0);
            $result['iva_display'] = CompanyPreference::formatMoneyForCompany($companyId, $result['data']['iva'] ?? 0);
            $result['total_display'] = CompanyPreference::formatMoneyForCompany($companyId, $result['data']['total'] ?? 0);  
            $result['amount_paid_display'] = CompanyPreference::formatMoneyForCompany($companyId, $result['data']['amount_paid'] ?? 0);
            $result['change_given_display'] = CompanyPreference::formatMoneyForCompany($companyId, $result['data']['change_given'] ?? 0);
            
            if(!empty($result['items']) && is_array($result['items'])) {
                $result['items'] = collect($result['items'])->map(function ($item) use ($companyId) {
                    $item['unit_price_display'] = CompanyPreference::formatMoneyForCompany($companyId, $item['unit_price'] ?? 0);
                    $item['line_subtotal_display'] = CompanyPreference::formatMoneyForCompany($companyId, $item['line_subtotal'] ?? 0);
                    return $item;
                })->values()->all();
            }

            return response()->json([
                'message' => 'Venta registrada correctamente.',
                'data' => $result,
            ], 201);
        } catch (Throwable $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            }

            return response()->json([
                'message' => 'No se pudo registrar la venta.',
            ], 422);
        }
    }
}