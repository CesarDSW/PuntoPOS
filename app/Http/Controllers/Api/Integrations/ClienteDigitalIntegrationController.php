<?php

namespace App\Http\Controllers\Api\Integrations;

use App\Http\Controllers\Controller;
use App\Models\ExternalIntegration;
use App\Models\ExternalSyncMap;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class ClienteDigitalIntegrationController extends Controller
{
    /**
     * Lista las integraciones registradas en Punto.
     */
    public function index()
    {
        $integrations = ExternalIntegration::query()
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $integrations,
        ]);
    }

    public function status(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado.',
                'data' => null,
            ], 401);
        }

        $integration = ExternalIntegration::query()
            ->where('source_app', 'clientedigital')
            ->where('company_idfk', $user->company_idfk)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        if (!$integration) {
            return response()->json([
                'success' => true,
                'message' => 'No hay integración activa con ClienteDigital.',
                'data' => [
                    'integration_id' => null,
                    'status' => 'inactive',
                    'external_base_url' => null,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Integración activa.',
            'data' => [
                'integration_id' => $integration->id,
                'status' => $integration->status,
                'external_base_url' => $integration->external_base_url,
                'last_products_sync_at' => optional($integration->last_products_sync_at)->format('Y-m-d H:i:s'),
                'last_sales_sync_at' => optional($integration->last_sales_sync_at)->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Conecta Punto con ClienteDigital usando el código generado en ClienteDigital.
     *
     * Body esperado:
     * {
     *   "external_base_url": "http://localhost/clientedigital/index.php/apis",
     *   "integration_code": "CD-PUNTO-XXXXXX",
     *   "company_idfk": 1,
     *   "branch_idfk": 1,
     *   "userr_idfk": 1
     * }
     */
    public function connect(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado.',
            ], 401);
        }

        $validated = $request->validate([
            'base_url' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:80'],
        ]);

        $baseUrl = $this->normalizeBaseUrl($validated['base_url']);

        $companyId = (int) $user->company_idfk;

        $branchId = session('selected_branch_id')
            ?? session('current_branch_id')
            ?? session('branch_id')
            ?? DB::table('user_branch')
                ->where('userr_idfk', $user->userr_id)
                ->value('sucursal_idfk');

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(15)
                ->post($baseUrl . '/punto_validate_code', [
                    'code' => $validated['code'],
                    'punto_company_id' => $companyId,
                    'punto_branch_id' => $branchId ? (int) $branchId : null,
                    'punto_user_id' => (int) $user->userr_id,
                ]);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo conectar con ClienteDigital.',
                'error' => $exception->getMessage(),
            ], 500);
        }

        $body = $response->json();

        if (!$response->successful() || !data_get($body, 'success')) {
            return response()->json([
                'success' => false,
                'message' => data_get($body, 'message', 'ClienteDigital rechazó el código.'),
                'clientedigital_status' => $response->status(),
                'clientedigital_response' => $body,
            ], 422);
        }

        $externalUserId = data_get($body, 'data.external_user_id');
        $accessToken = data_get($body, 'data.access_token');

        if (!$externalUserId || !$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'ClienteDigital no devolvió external_user_id o access_token.',
                'clientedigital_response' => $body,
            ], 422);
        }

        $integration = ExternalIntegration::updateOrCreate(
            [
                'source_app' => 'clientedigital',
                'company_idfk' => $companyId,
                'external_user_id' => (int) $externalUserId,
            ],
            [
                'branch_idfk' => $branchId ? (int) $branchId : null,
                'userr_idfk' => (int) $user->userr_id,
                'external_base_url' => $baseUrl,
                'access_token' => $accessToken,
                'status' => 'active',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Integración con ClienteDigital guardada correctamente.',
            'data' => [
                'integration' => $this->formatIntegration($integration),
            ],
        ]);
    }

    /**
     * Sincroniza productos desde ClienteDigital hacia Punto.
     *
     * Endpoint esperado en ClienteDigital:
     * GET /punto_products
     */
    public function syncProducts(Request $request, ExternalIntegration $integration)
    {
        if ($integration->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'La integración no está activa.',
            ], 422);
        }

        $limit = (int) $request->input('limit', 100);
        $offset = (int) $request->input('offset', 0);

        if ($limit <= 0) {
            $limit = 100;
        }

        if ($limit > 500) {
            $limit = 500;
        }

        if ($offset < 0) {
            $offset = 0;
        }

        try {
            $response = Http::acceptJson()
                ->withToken($integration->access_token)
                ->timeout(20)
                ->get($integration->products_endpoint, [
                    'limit' => $limit,
                    'offset' => $offset,
                ]);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo consultar productos en ClienteDigital.',
                'error' => $exception->getMessage(),
            ], 500);
        }

        $body = $response->json();

        if (!$response->successful() || !data_get($body, 'success')) {
            return response()->json([
                'success' => false,
                'message' => data_get($body, 'message', 'ClienteDigital no devolvió productos correctamente.'),
                'clientedigital_status' => $response->status(),
                'clientedigital_response' => $body,
            ], 422);
        }

        $products = data_get($body, 'data.products', []);

        $result = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'items' => [],
        ];

        foreach ($products as $externalProduct) {
            try {
                $synced = $this->syncOneProduct($integration, $externalProduct);

                if ($synced['action'] === 'created') {
                    $result['created']++;
                } elseif ($synced['action'] === 'updated') {
                    $result['updated']++;
                } else {
                    $result['skipped']++;
                }

                $result['items'][] = $synced;
            } catch (Throwable $exception) {
                $result['failed']++;

                $result['items'][] = [
                    'external_id' => data_get($externalProduct, 'external_id'),
                    'action' => 'failed',
                    'message' => $exception->getMessage(),
                ];
            }
        }

        $integration->update([
            'last_products_sync_at' => now(),
        ]);

        app(NotificationService::class)->syncCurrentInventoryStatus(
            companyId: (int) $integration->company_idfk,
            branchId: (int) $integration->branch_idfk
        );

        return response()->json([
            'success' => true,
            'message' => 'Sincronización de productos finalizada.',
            'data' => [
                'integration' => $this->formatIntegration($integration->fresh()),
                'clientedigital_pagination' => data_get($body, 'data.pagination'),
                'summary' => $result,
            ],
        ]);
    }

    /**
     * Sincroniza ventas desde ClienteDigital hacia Punto.
     * 
     * Endpoint esperado en ClienteDigital:
     * GET /punto_sales
     */
    public function syncSales(Request $request, ExternalIntegration $integration)
    {
        if ($integration->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'La integración no está activa.',
            ], 422);
        }

        if (!$integration->company_idfk || !$integration->branch_idfk || !$integration->userr_idfk) {
            return response()->json([
                'success' => false,
                'message' => 'La integración necesita company_idfk, branch_idfk y userr_idfk para importar ventas.',
            ], 422);
        }

        $limit = (int) $request->input('limit', 100);
        $offset = (int) $request->input('offset', 0);

        if ($limit <= 0) {
            $limit = 100;
        }

        if ($limit > 500) {
            $limit = 500;
        }

        if ($offset < 0) {
            $offset = 0;
        }

        $query = [
            'limit' => $limit,
            'offset' => $offset,
        ];

        if ($request->filled('date_from')) {
            $query['date_from'] = $request->input('date_from');
        }

        if ($request->filled('date_to')) {
            $query['date_to'] = $request->input('date_to');
        }

        try {
            $response = Http::acceptJson()
                ->withToken($integration->access_token)
                ->timeout(20)
                ->get($integration->sales_endpoint, $query);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo consultar ventas en ClienteDigital.',
                'error' => $exception->getMessage(),
            ], 500);
        }

        $body = $response->json();

        if (!$response->successful() || !data_get($body, 'success')) {
            return response()->json([
                'success' => false,
                'message' => data_get($body, 'message', 'ClienteDigital no devolvio ventas correctamente.'),
                'clientedigital_status' => $response->status(),
                'clientedigital_response' => $body,
            ], 422);
        }

        $sales = data_get($body, 'data.sales', []);

        $result = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'items' => [],
        ];

        foreach ($sales as $externalSale) {
            try {
                $synced = $this->syncOneSale($integration, $externalSale);

                if ($synced['action'] === 'created') {
                    $result['created']++;
                } elseif ($synced['action'] === 'updated') {
                    $result['updated']++;
                } else {
                    $result['skipped']++;
                }

                $result['items'][] = $synced;
            } catch (Throwable $exception) {
                $result['failed']++;

                $result['items'][] = [
                    'external_id' => data_get($externalSale, 'external_id'),
                    'action' => 'failed',
                    'message' => $exception->getMessage(),
                ];
            }
        }

        $integration->update([
            'last_sales_sync_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sincronización de ventas finalizada.',
            'data' => [
                'integration' => $this->formatIntegration($integration->fresh()),
                'clientedigital_pagination' => data_get($body, 'data.pagination'),
                'summary' => $result,
            ],
        ]);
    }

    /**
     * Sincroniza un solo producto externo hacia productt y branch_product_stock.
     */
    private function syncOneProduct(ExternalIntegration $integration, array $externalProduct): array
    {
        $externalId = (string) data_get($externalProduct, 'external_id');

        if ($externalId === '') {
            return [
                'external_id' => null,
                'product_id' => null,
                'action' => 'skipped',
                'message' => 'Producto externo sin external_id.',
            ];
        }

        return DB::transaction(function () use ($integration, $externalProduct, $externalId) {
            $map = ExternalSyncMap::query()
                ->where('external_integration_id', $integration->id)
                ->where('entity_type', 'product')
                ->where('external_id', $externalId)
                ->first();

            $name = $this->limitText(
                data_get($externalProduct, 'name') ?: 'Producto ClienteDigital ' . $externalId,
                80
            );

            $description = $this->limitText(
                data_get($externalProduct, 'description') ?: 'Importado desde ClienteDigital',
                250
            );

            $price = (float) data_get($externalProduct, 'price', 0);
            $stock = max(0, (int) data_get($externalProduct, 'stock', 0));
            $status = ((int) data_get($externalProduct, 'status', 1)) ? 1 : 0;

            $categoryName = data_get($externalProduct, 'category') ?: 'General';
            $categoryId = $this->resolveProductCategory($categoryName);

            $productData = [
                'name_product' => $name,
                'description_product' => $description,
                'price' => $price,
                'status_product' => $status,
                'company_idfk' => (int) $integration->company_idfk,
                'category_idfk' => $categoryId,
            ];

            $productId = null;
            $action = 'created';

            if ($map) {
                $existingMappedProduct = DB::table('productt')
                    ->where('product_id', $map->local_id)
                    ->first();

                if ($existingMappedProduct) {
                    DB::table('productt')
                        ->where('product_id', $map->local_id)
                        ->update($productData);

                    $productId = (int) $map->local_id;
                    $action = 'updated';
                }
            }

            if (!$productId) {
                $code = $this->makeProductCode(
                    data_get($externalProduct, 'code'),
                    $externalId,
                    (int) $integration->company_idfk
                );

                $existingProductByCode = DB::table('productt')
                    ->where('company_idfk', (int) $integration->company_idfk)
                    ->where('code_product', $code)
                    ->first();

                $productData['code_product'] = $code;

                if ($existingProductByCode) {
                    DB::table('productt')
                        ->where('product_id', $existingProductByCode->product_id)
                        ->update($productData);

                    $productId = (int) $existingProductByCode->product_id;
                    $action = 'updated';
                } else {
                    $productId = DB::table('productt')->insertGetId($productData, 'product_id');
                    $action = 'created';
                }

                ExternalSyncMap::updateOrCreate(
                    [
                        'external_integration_id' => $integration->id,
                        'entity_type' => 'product',
                        'external_id' => $externalId,
                    ],
                    [
                        'local_table' => 'productt',
                        'local_id' => $productId,
                    ]
                );
            }

            if ($integration->branch_idfk) {
                DB::table('branch_product_stock')->updateOrInsert(
                    [
                        'branch_idfk' => (int) $integration->branch_idfk,
                        'product_idfk' => (int) $productId,
                    ],
                    [
                        'stocks' => $stock,
                        'minimum_stock' => 0,
                        'status_stock' => $status,
                    ]
                );
            }

            return [
                'external_id' => $externalId,
                'product_id' => $productId,
                'action' => $action,
                'name' => $name,
                'stock' => $stock,
            ];
        });
    }

    /**
     * Busca o crea categoría tipo PRODUCTO.
     */
    private function resolveProductCategory(?string $categoryName): int
    {
        $categoryName = trim((string) $categoryName);

        if ($categoryName === '') {
            $categoryName = 'General';
        }

        $categoryName = $this->limitText($categoryName, 15);

        $category = DB::table('category')
            ->where('name_category', $categoryName)
            ->where('type_category', 'PRODUCTO')
            ->first();

        if ($category) {
            return (int) $category->category_id;
        }

        return DB::table('category')->insertGetId([
            'name_category' => $categoryName,
            'type_category' => 'PRODUCTO',
        ], 'category_id');
    }

    private function makeServiceCode($rawCode, string $externalId, int $companyId): string
    {
        $raw = strtoupper(trim((string) $rawCode));

        if ($raw === '') {
            $raw = 'CD-S-' . $externalId;
        }

        $raw = preg_replace('/[^A-Z0-9\-]/', '', $raw);

        if ($raw === '') {
            $raw = 'CD-S-' . $externalId;
        }

        $candidate = $this->limitText($raw, 15);

        $exists = DB::table('servicee')
            ->where('company_idfk', $companyId)
            ->where('code_service', $candidate)
            ->exists();

        if (!$exists) {
            return $candidate;
        }

        $fallback = $this->limitText('CD-S-' . $externalId, 15);

        $existsFallback = DB::table('servicee')
            ->where('company_idfk', $companyId)
            ->where('code_service', $fallback)
            ->exists();

        if (!$existsFallback) {
            return $fallback;
        }

        for ($i = 1; $i <= 99; $i++) {
            $hash = strtoupper(substr(md5($externalId . '-service-' . $i), 0, 6));
            $candidate = $this->limitText('CD-S-' . $hash, 15);

            $existsCandidate = DB::table('servicee')
                ->where('company_idfk', $companyId)
                ->where('code_service', $candidate)
                ->exists();

            if (!$existsCandidate) {
                return $candidate;
            }
        }

        return $this->limitText('CD-S-' . Str::random(6), 15);
    }

    /**
     * Genera código compatible con productt.code_product.
     * En Punto el código máximo es de 15 caracteres.
     */
    private function makeProductCode(?string $incomingCode, string $externalId, int $companyId): string
    {
        $raw = strtoupper(trim((string) $incomingCode));

        if ($raw === '') {
            $raw = 'CD-' . $externalId;
        }

        $raw = preg_replace('/[^A-Z0-9\-]/', '', $raw);

        if ($raw === '') {
            $raw = 'CD-' . $externalId;
        }

        $candidate = $this->limitText($raw, 15);

        $exists = DB::table('productt')
            ->where('company_idfk', $companyId)
            ->where('code_product', $candidate)
            ->exists();

        if (!$exists) {
            return $candidate;
        }

        $fallback = $this->limitText('CD-' . $externalId, 15);

        $existsFallback = DB::table('productt')
            ->where('company_idfk', $companyId)
            ->where('code_product', $fallback)
            ->exists();

        if (!$existsFallback) {
            return $fallback;
        }

        for ($i = 1; $i <= 99; $i++) {
            $hash = strtoupper(substr(md5($externalId . '-' . $i), 0, 6));
            $candidate = $this->limitText('CD-' . $hash, 15);

            $existsCandidate = DB::table('productt')
                ->where('company_idfk', $companyId)
                ->where('code_product', $candidate)
                ->exists();

            if (!$existsCandidate) {
                return $candidate;
            }
        }

        return $this->limitText('CD-' . Str::random(8), 15);
    }

    /**
     * Sincroniza una venta externa hacia sale, saleitem, payments.
     */
    private function syncOneSale(ExternalIntegration $integration, array $externalSale): array
    {
        $externalId = (string) data_get($externalSale, 'external_id');
        
        if ($externalId === '') {
            return [
                'external_id' => null,
                'sale_id' => null,
                'action' => 'skipped',
                'message' => 'Venta externa sin external_id.',
            ];
        }

        return DB::transaction(function () use ($integration, $externalSale, $externalId) {
            $map = ExternalSyncMap::query()
                ->where('external_integration_id', $integration->id)
                ->where('entity_type', 'sale')
                ->where('external_id', $externalId)
                ->first();

            if ($map) {
                $existingSale = DB::table('sale')
                    ->where('sale_id', $map->local_id)
                    ->first();
                
                if ($existingSale) {
                    return [
                        'external_id' => $externalId,
                        'sale_id' => (int) $map->local_id,
                        'action' => 'skipped',
                        'message' => 'La venta ya había sido sincronizada.'
                    ];
                }

                $map->delete();
            }

            $items = data_get($externalSale, 'items', []);

            if (empty($items)) {
                throw new  \RuntimeException('La venta externa no tiene productos en items.');
            }
            
            $customerId = $this->resolveIntegrationCustomer($integration);

            $subtotal = (float) data_get($externalSale, 'subtotal', 0);
            $discountFixed = (float) data_get($externalSale, 'discount_fixed', 0);
            $discountPercent = (float) data_get($externalSale, 'discount_percent', 0);

            $discount = $discountFixed;

            if ($discount <= 0 && $discountPercent > 0) {
                $discount = round($subtotal * ($discountPercent / 100), 4);
            }

            $total = (float) data_get($externalSale, 'total', 0);
            $dateTime = $this->normalizeSaleDateTime(data_get($externalSale, 'sold_at'));
            $statusSale = $this->mapSaleStatus(data_get($externalSale, 'status'));
            $paymentMethod = $this->mapPaymentMethod(data_get($externalSale, 'payment_method'));

            $saleId = DB::table('sale')->insertGetId([
                'date_time' => $dateTime,
                'company_idfk' => (int) $integration->company_idfk,
                'branch_idfk' => (int) $integration->branch_idfk,
                'cashier_userr_idfk' => (int) $integration->userr_idfk,
                'customer_idfk' => $customerId,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'status_sale' => $statusSale,
            ], 'sale_id');

            $createdItems = 0;

            foreach ($items as $item) {
                $itemStatus = $this->normalizeExternalItemStatus(data_get($item, 'status', 1));

                if (!$itemStatus) {
                    continue;
                }

                $itemTypeValue = data_get($item, 'type', 'Producto');

                if (is_array($itemTypeValue)) {
                    $itemTypeValue = $itemTypeValue['name'] ?? $itemTypeValue['value'] ?? reset($itemTypeValue);
                }

                $type = strtoupper(trim((string) $itemTypeValue));

                if (in_array($type, ['PRODUCT', 'PRODUCTO', 'PRODUCTOS'], true)) {
                    $type = 'PRODUCTO';
                }

                if (in_array($type, ['SERVICE', 'SERVICIO', 'SERVICIOS'], true)) {
                    $type = 'SERVICIO';
                }

                $quantity = max(1, (int) data_get($item, 'quantity', 1));
                $unitPrice = (float) data_get($item, 'unit_price', 0);
                $totalLine = (float) data_get($item, 'total_line', $quantity * $unitPrice);

                $productId = null;
                $serviceId = null;

                if ($type === 'PRODUCTO') {
                    $externalProductId = (string) data_get($item, 'external_product_id');

                    if ($externalProductId === '') {
                        throw new \RuntimeException('Un detalle de venta no tiene external_product_id.');
                    }

                    $productMap = ExternalSyncMap::query()
                        ->where('external_integration_id', $integration->id)
                        ->where('entity_type', 'product')
                        ->where('external_id', $externalProductId)
                        ->first();

                    if (!$productMap) {
                        throw new \RuntimeException(
                            'El producto externo ' . $externalProductId . ' no está sincronizado. Sincroniza productos primero.'
                        );
                    }

                    $localProduct = DB::table('productt')
                        ->where('product_id', $productMap->local_id)
                        ->first();

                    if (!$localProduct) {
                        throw new \RuntimeException(
                            'El producto local relacionado al externo ' . $externalProductId . ' ya no existe.'
                        );
                    }

                    $productId = (int) $productMap->local_id;
                }

                if ($type === 'SERVICIO') {
                    $serviceId = $this->resolveExternalServiceForSale($integration, $item);
                }

                if (!in_array($type, ['PRODUCTO', 'SERVICIO'], true)) {
                    throw new \RuntimeException('Tipo de detalle no soportado: ' . $type);
                }

                $saleItemId = DB::table('saleitem')->insertGetId([
                    'sale_idfk' => $saleId,
                    'item_type' => $type,
                    'product_idfk' => $productId,
                    'service_idfk' => $serviceId,
                    'amount' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => 0,
                    'total_line' => $totalLine,
                ], 'saleitem_id');

                $externalSaleItemId = (string) data_get($item, 'external_id', $externalId . '-' . $createdItems);

                ExternalSyncMap::updateOrCreate(
                    [
                        'external_integration_id' => $integration->id,
                        'entity_type' => 'sale_item',
                        'external_id' => $externalSaleItemId,
                    ],
                    [
                        'local_table' => 'saleitem',
                        'local_id' => $saleItemId,
                    ]
                );

                $createdItems++;
            }

            if ($createdItems <= 0) {
                throw new \RuntimeException('La venta no tiene detalles válidos para importar.');
            }

            DB::table('payments')->insert([
                'date_time' => $dateTime,
                'payment_method' => $paymentMethod,
                'status_payment' => $statusSale === 'PAGADA' ? 'PAGADO' : $statusSale,
                'commission' => 0,
                'sale_idfk' => $saleId,
                'customer_idfk' => $customerId,
            ]);

            ExternalSyncMap::updateOrCreate(
                [
                    'external_integration_id' => $integration->id,
                    'entity_type' => 'sale',
                    'external_id' => $externalId,
                ],
                [
                    'local_table' => 'sale',
                    'local_id' => $saleId,
                ]
            );

            /*
            * Nota:
            * No descontamos stock aquí para evitar doble descuento.
            * La sincronización de productos ya trae el stock desde ClienteDigital.
            */

            return [
                'external_id' => $externalId,
                'sale_id' => $saleId,
                'action' => 'created',
                'items_created' => $createdItems,
                'total' => $total,
            ];
        });
    }

    private function resolveExternalServiceForSale(ExternalIntegration $integration, array $item): int
    {
        $externalServiceId = (string) (
            data_get($item, 'external_service_id')
            ?: data_get($item, 'external_product_id')
            ?: data_get($item, 'external_id')
        );

        if ($externalServiceId === '') {
            throw new \RuntimeException('Un detalle de servicio no tiene external_service_id.');
        }

        $serviceMap = ExternalSyncMap::query()
            ->where('external_integration_id', $integration->id)
            ->where('entity_type', 'service')
            ->where('external_id', $externalServiceId)
            ->first();

        if ($serviceMap) {
            $existingService = DB::table('servicee')
                ->where('service_id', $serviceMap->local_id)
                ->first();

            if ($existingService) {
                return (int) $serviceMap->local_id;
            }

            $serviceMap->delete();
        }

        $serviceName = $this->limitText(
            data_get($item, 'name')
            ?: data_get($item, 'description')
            ?: data_get($item, 'product_name')
            ?: 'Servicio ClienteDigital ' . $externalServiceId,
            80
        );

        $serviceDescription = $this->limitText(
            data_get($item, 'description')
            ?: 'Servicio importado desde ClienteDigital',
            250
        );

        $servicePrice = (float) data_get($item, 'unit_price', 0);

        $categoryId = $this->resolveProductCategory('Servicios');

        $serviceData = [
            'name_service' => $serviceName,
            'description_service' => $serviceDescription,
            'price' => $servicePrice,
            'status_service' => 1,
            'company_idfk' => (int) $integration->company_idfk,
            'category_idfk' => $categoryId,
        ];

        $code = $this->makeServiceCode(
            data_get($item, 'code'),
            $externalServiceId,
            (int) $integration->company_idfk
        );

        $existingServiceByCode = DB::table('servicee')
            ->where('company_idfk', (int) $integration->company_idfk)
            ->where('code_service', $code)
            ->first();

        if ($existingServiceByCode) {
            DB::table('servicee')
                ->where('service_id', $existingServiceByCode->service_id)
                ->update($serviceData);

            $serviceId = (int) $existingServiceByCode->service_id;
        } else {
            $serviceData['code_service'] = $code;

            $serviceId = DB::table('servicee')->insertGetId($serviceData, 'service_id');
        }

        ExternalSyncMap::updateOrCreate(
            [
                'external_integration_id' => $integration->id,
                'entity_type' => 'service',
                'external_id' => $externalServiceId,
            ],
            [
                'local_table' => 'servicee',
                'local_id' => $serviceId,
            ]
        );

        return $serviceId;
    }

    /**
     * Crea o reutiliza un cliente genérico para ventas importadas.
     */
    private function resolveIntegrationCustomer(ExternalIntegration $integration): int
    {
        $email = 'clientedigital-' . $integration->id . '@sync.local';

        $customer = DB::table('customer')
            ->where('company_idfk', (int) $integration->company_idfk)
            ->where('email', $email)
            ->first();

        if ($customer) {
            return (int) $customer->customer_id;
        }

        return DB::table('customer')->insertGetId([
            'name_customer' => 'ClienteDigital',
            'phone' => '0000000000',
            'email' => $email,
            'company_idfk' => (int) $integration->company_idfk,
        ], 'customer_id');
    }

    /**
     * Convierte fecha externa a formato MySQL.
     */
    private function normalizeSaleDateTime($value): string
    {
        if (!$value) {
            return now()->format('Y-m-d H:i:s');
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (Throwable $exception) {
            return now()->format('Y-m-d H:i:s');
        }
    }

    /**
     * Convierte estado externo a estado de Punto.
     */
    private function mapSaleStatus($status): string
    {
        if (is_array($status)) {
            $status = $status['status'] ?? $status['value'] ?? reset($status);
        }

        $status = strtolower(trim((string) $status));

        if ($status === 'cancelled' || $status === 'canceled' || $status === 'cancelada') {
            return 'CANCELADA';
        }

        if ($status === 'returned' || $status === 'devuelta') {
            return 'DEVUELTA';
        }

        return 'PAGADA';
    }

    private function normalizeExternalItemStatus($status): bool
    {
        if (is_bool($status)) {
            return $status;
        }

        if (is_numeric($status)) {
            return (int) $status === 1;
        }

        $status = strtolower(trim((string) $status));

        if ($status === '') {
            return true;
        }

        return in_array($status, [
            '1',
            'active',
            'activo',
            'enabled',
            'habilitado',
            'valid',
            'valido',
            'válido',
            'paid',
            'pagado',
            'completed',
            'completado',
            'vendido',
        ], true);
    }

    /**
     * Convierte forma de pago externa a forma de pago de Punto.
     */
    private function mapPaymentMethod($paymentMethod): string
    {
        if (is_array($paymentMethod)) {
            $paymentMethod = $paymentMethod['name']
                ?? $paymentMethod['method']
                ?? $paymentMethod['value']
                ?? reset($paymentMethod);
        }

        $paymentMethod = strtoupper(trim((string) $paymentMethod));

        if ($paymentMethod === '') {
            return 'EFECTIVO';
        }

        return match ($paymentMethod) {
            '1', 'CASH', 'EFECTIVO', 'CONTADO', 'DINERO' => 'EFECTIVO',
            '2', 'CARD', 'TARJETA', 'TARJETA DE CREDITO', 'TARJETA DE CRÉDITO', 'DEBITO', 'DÉBITO', 'CREDITO', 'CRÉDITO' => 'TARJETA',
            '3', 'TRANSFER', 'TRANSFERENCIA', 'SPEI', 'TRANSFERENCIA BANCARIA' => 'TRANSFERENCIA',
            '4', 'CHEQUE', 'VALE', 'VALES', 'VALES DE DESPENSA' => 'CHEQUE',
            default => $this->limitText($paymentMethod, 70),
        };
    }

    private function normalizeBaseUrl(string $url): string
    {
        $url = rtrim(trim($url), '/');

        $suffixes = [
            '/punto_validate_code',
            '/punto_products',
            '/punto_sales',
        ];

        foreach ($suffixes as $suffix) {
            if (Str::endsWith($url, $suffix)) {
                $url = substr($url, 0, -strlen($suffix));
            }
        }

        return rtrim($url, '/');
    }

    private function limitText($value, int $limit): string
    {
        if (is_array($value)) {
            $value = $value['name'] ?? $value['value'] ?? reset($value);
        }

        $value = trim((string) $value);

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $limit);
        }

        return substr($value, 0, $limit);
    }

    private function formatIntegration(ExternalIntegration $integration): array
    {
        return [
            'id' => $integration->id,
            'company_idfk' => $integration->company_idfk,
            'branch_idfk' => $integration->branch_idfk,
            'userr_idfk' => $integration->userr_idfk,
            'source_app' => $integration->source_app,
            'external_user_id' => $integration->external_user_id,
            'external_base_url' => $integration->external_base_url,
            'status' => $integration->status,
            'last_products_sync_at' => optional($integration->last_products_sync_at)->format('Y-m-d H:i:s'),
            'last_sales_sync_at' => optional($integration->last_sales_sync_at)->format('Y-m-d H:i:s'),
            'created_at' => optional($integration->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($integration->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}