<?php

namespace App\Services;

use App\Models\CompanySettings;
use App\Models\SystemNotification;

class NotificationService
{
    public function handleStockChanged(
        int $companyId,
        ?int $branchId,
        int $productId,
        string $productName,
        int $oldStock,
        int $newStock,
        int $minimumStock
    ): void {
        $settings = CompanySettings::where('company_idfk', $companyId)->first();

        if(!$settings) {
            return;
        }

        //Si el stock esta arriba del minimo se borran las alertas anteriores
        //para permitir futuras alertas del mismo producto.
        if ($newStock > $minimumStock) {
            SystemNotification::whereIn('dedupe_key', [
                $this->buildDedupeKey('LOW_STOCK', $companyId, $branchId, $productId),
                $this->buildDedupeKey('OUT_OF_STOCK', $companyId, $branchId, $productId),
            ])->delete();
        }

        //Si el producto agotado pasó de más de 0 a 0
        if(
            $settings->notify_out_of_stock &&
            $oldStock > 0 &&
            $newStock <= 0
        ) {
            $this->createNotification(
                companyId: $companyId,
                branchId: $branchId,
                typeCode: 'OUT_OF_STOCK',
                title: 'Producto agotado',
                message: "El producto {$productName} se quedó sin existencias.",
                referenceType: 'PRODUCT',
                referenceId: $productId,
                dedupeKey: $this->buildDedupeKey('OUT_OF_STOCK', $companyId, $branchId, $productId),
            );
        }

        //Si es stock bajo, quedo por debajo o igual al mínimo, pero aun no esta agotado
        if(
            $settings->notify_low_stock &&
            $newStock > 0 &&
            $newStock <= $minimumStock &&
            $oldStock > $minimumStock
        ) {
            $this->createNotification(
                companyId: $companyId,
                branchId: $branchId,
                typeCode: 'LOW_STOCK',
                title: 'Stock bajo',
                message: "El producto {$productName} llego a stock bajo ({$newStock} unidades).",
                referenceType: 'PRODUCT',
                referenceId: $productId,
                dedupeKey: $this->buildDedupeKey('LOW_STOCK', $companyId, $branchId, $productId),
            );
        }
    }

    private function createNotification(
        int $companyId,
        ?int $branchId,
        string $typeCode,
        string $title,
        string $message,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $dedupeKey = null
    ): void {
        if($dedupeKey) {
            $exists = SystemNotification::where('dedupe_key', $dedupeKey)->exists();

            if ($exists) {
                return;
            }
        }

        SystemNotification::create([
            'company_idfk' => $companyId,
            'branch_idfk' => $branchId,
            'type_code' => $typeCode,
            'title' => $title,
            'message' => $message,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'dedupe_key' => $dedupeKey,
            'is_read' => false,
            'created_at' => now(),
            'read_at' => null,
        ]);
    }

    private function buildDedupeKey(
        string $typeCode,
        int $companyId,
        ?int $branchId,
        int $productId
    ): string {
        $branchPart = $branchId ?? 0;

        return strtolower("{$typeCode}_{$companyId}_{$branchPart}_{$productId}");
    }

   /* public function saleCancelled(
        int $companyId,
        ?int $branchId,
        int $saleId,
        float $total,
        string $cancelledBy
    ): void{
        $settings = CompanySettings::where('company_idfk', $companyId)->first();

        if(!$settings || !$settings->notify_sale_cancelled) {
            return;
        }

        $this->createNotification(
            companyId: $companyId,
            branchId: $branchId,
            typeCode: 'SALE_CANCELLED',
            title: 'Venta cancelada',
            message: "La venta #{$saleId} fue cancelada por {$cancelledBy}. Total: $" . number_format($total, 2),
            referenceType: 'SALE',
            referenceId: $saleId,
            dedupeKey: "sale_cancelled_{$companyId}_{$saleId}"
        );
    }

    private function createNotification(
        int $companyId,
        ?int $branchId,
        string $typeCode,
        string $title,
        string $message,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $dedupeKey = null
    ): void {
        if ($dedupeKey) {
            $exists = SystemNotification::where('dedupe_key', $dedupeKey)->exists();

            if ($exists) {
                return;
            }
        }

        SystemNotification::create([
            'company_idfk' => $companyId,
            'branch_idfk' => $branchId,
            'type_code' => $typeCode,
            'title' => $title,
            'message' => $message,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'dedupe_key' => $dedupeKey,
            'is_read' => false,
            'created_at' => now(),
            'read_at' => null,
        ]);
    } */
} 