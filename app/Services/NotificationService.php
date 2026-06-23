<?php

namespace App\Services;

use App\Models\CompanySettings;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

        if (!$settings) {
            return;
        }

        if ($newStock > $minimumStock && $newStock > 0) {
            SystemNotification::whereIn('dedupe_key', [
                $this->buildDedupeKey('LOW_STOCK', $companyId, $branchId, $productId),
                $this->buildDedupeKey('OUT_OF_STOCK', $companyId, $branchId, $productId),
            ])->delete();

            return;
        }

        if ($settings->notify_out_of_stock && $newStock <= 0) {
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

            return;
        }

        if (
            $settings->notify_low_stock &&
            $newStock > 0 &&
            $minimumStock > 0 &&
            $newStock <= $minimumStock
        ) {
            $this->createNotification(
                companyId: $companyId,
                branchId: $branchId,
                typeCode: 'LOW_STOCK',
                title: 'Stock bajo',
                message: "El producto {$productName} llegó a stock bajo ({$newStock} unidades, mínimo: {$minimumStock}).",
                referenceType: 'PRODUCT',
                referenceId: $productId,
                dedupeKey: $this->buildDedupeKey('LOW_STOCK', $companyId, $branchId, $productId),
            );
        }
    }

    public function syncCurrentInventoryStatus(int $companyId, ?int $branchId = null): void
    {
        $stockQuery = DB::table('branch_product_stock as bps')
            ->join('productt as p', 'p.product_id', '=', 'bps.product_idfk')
            ->where('p.company_idfk', $companyId)
            ->where('p.status_product', 1)
            ->where('bps.status_stock', 1)
            ->select([
                'p.product_id',
                'p.name_product',
                'bps.branch_idfk',
                'bps.stocks',
                'bps.minimum_stock',
            ]);

        if ($branchId) {
            $stockQuery->where('bps.branch_idfk', $branchId);
        }

        $products = $stockQuery->get();

        foreach ($products as $product) {
            $currentStock = (int) ($product->stocks ?? 0);
            $minimumStock = (int) ($product->minimum_stock ?? 0);

            if ($currentStock <= 0 || ($minimumStock > 0 && $currentStock <= $minimumStock)) {
                $this->handleStockChanged(
                    companyId: $companyId,
                    branchId: (int) $product->branch_idfk,
                    productId: (int) $product->product_id,
                    productName: (string) $product->name_product,
                    oldStock: $currentStock,
                    newStock: $currentStock,
                    minimumStock: $minimumStock
                );
            }
        }
    }

    public function saleCancelled(
        int $companyId,
        ?int $branchId,
        int $saleId,
        float $total,
        string $cancelledBy
    ): void {
        $settings = CompanySettings::where('company_idfk', $companyId)->first();

        if (!$settings || !$settings->notify_sale_cancelled) {
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

    public function salePending(
        int $companyId,
        ?int $branchId,
        int $saleId,
        float $total
    ): void {
        $settings = CompanySettings::where('company_idfk', $companyId)->first();

        if (!$settings || !($settings->notify_sale_pending ?? false)) {
            return;
        }

        $this->createNotification(
            companyId: $companyId,
            branchId: $branchId,
            typeCode: 'SALE_PENDING',
            title: 'Venta pendiente',
            message: "La venta #{$saleId} quedó pendiente. Total: $" . number_format($total, 2),
            referenceType: 'SALE',
            referenceId: $saleId,
            dedupeKey: "sale_pending_{$companyId}_{$saleId}"
        );
    }

    public function saleCompleted(
        int $companyId,
        ?int $branchId,
        int $saleId,
        float $total
    ): void {
        $settings = CompanySettings::where('company_idfk', $companyId)->first();

        if (!$settings || !($settings->notify_sale_completed ?? false)) {
            return;
        }

        $this->createNotification(
            companyId: $companyId,
            branchId: $branchId,
            typeCode: 'SALE_COMPLETED',
            title: 'Venta completada',
            message: "La venta #{$saleId} se realizó correctamente. Total: $" . number_format($total, 2),
            referenceType: 'SALE',
            referenceId: $saleId,
            dedupeKey: "sale_completed_{$companyId}_{$saleId}"
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
        ?string $dedupeKey = null,
        ?int $targetUserId = null,
        ?string $actionUrl = null
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
            'target_user_idfk' => $targetUserId,
            'type_code' => $typeCode,
            'title' => $title,
            'message' => $message,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'action_url' => $actionUrl,
            'dedupe_key' => $dedupeKey,
            'is_read' => false,
            'created_at' => now(),
            'read_at' => null,
        ]);
    }

    public function supportTicketCreated(\App\Models\SupportTicket $ticket): void
    {
        $ticket->loadMissing('user');

        $companyId = (int) ($ticket->user->company_idfk ?? 0);

        if ($companyId <= 0) {
            return;
        }

        $developerRoleId = DB::table('rol')
            ->where('type_rol', 'DEV')
            ->value('rol_id');

        if (!$developerRoleId) {
            return;
        }

        $developers = User::where('rol_idfk', $developerRoleId)
            ->where('state', 1)
            ->get();

        foreach ($developers as $developer) {
            $this->createNotification(
                companyId: $companyId,
                branchId: $ticket->branch_id ? (int) $ticket->branch_id : null,
                targetUserId: (int) $developer->userr_id,
                typeCode: 'SUPPORT_TICKET',
                title: 'Nuevo ticket de soporte',
                message: "El usuario {$ticket->user->name_user} envió un ticket: {$ticket->subject}.",
                referenceType: 'SUPPORT_TICKET',
                referenceId: (int) $ticket->id,
                actionUrl: route('developer.support.show', $ticket->id),
                dedupeKey: "support_ticket_{$ticket->id}_dev_{$developer->userr_id}"
            );
        }
    }

    public function supportTicketAnswered(\App\Models\SupportTicket $ticket): void
    {
        $ticket->loadMissing('user');

        $companyId = (int) ($ticket->user->company_idfk ?? 0);

        if ($companyId <= 0) {
            return;
        }

        $this->createNotification(
            companyId: $companyId,
            branchId: $ticket->branch_id ? (int) $ticket->branch_id : null,
            targetUserId: (int) $ticket->user_id,
            typeCode: 'SUPPORT_REPLY',
            title: 'Respuesta de soporte',
            message: "Un desarrollador respondió tu ticket: {$ticket->subject}.",
            referenceType: 'SUPPORT_TICKET',
            referenceId: (int) $ticket->id,
            actionUrl: url('/dashboard?support_ticket=' . $ticket->id),
            dedupeKey: "support_reply_{$ticket->id}_" . now()->timestamp
        );
    }

    public function supportTicketUserReplied(\App\Models\SupportTicket $ticket): void
    {
        $ticket->loadMissing('user');

        $companyId = (int) ($ticket->user->company_idfk ?? 0);

        if ($companyId <= 0) {
            return;
        }

        $developerRoleId = DB::table('rol')
            ->where('type_rol', 'DEV')
            ->value('rol_id');

        if (!$developerRoleId) {
            return;
        }

        $developers = User::where('rol_idfk', $developerRoleId)
            ->where('state', 1)
            ->get();

        foreach ($developers as $developer) {
            $this->createNotification(
                companyId: $companyId,
                branchId: $ticket->branch_id ? (int) $ticket->branch_id : null,
                targetUserId: (int) $developer->userr_id,
                typeCode: 'SUPPORT_TICKET_REPLY',
                title: 'Nuevo mensaje de soporte',
                message: "El usuario {$ticket->user->name_user} respondió el ticket: {$ticket->subject}.",
                referenceType: 'SUPPORT_TICKET',
                referenceId: (int) $ticket->id,
                actionUrl: route('developer.support.show', $ticket->id),
                dedupeKey: "support_user_reply_{$ticket->id}_" . now()->timestamp . "_dev_{$developer->userr_id}"
            );
        }
    }
}