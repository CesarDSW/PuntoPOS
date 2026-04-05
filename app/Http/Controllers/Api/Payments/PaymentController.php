<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Api\Ventas\SalesBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends SalesBaseController
{
    public function summary(Request $request)
    {
        $companyId = $this->getCompanyId();
        $branchId = $this->resolvePaymentsBranchId($request, $companyId);
        $today = now()->toDateString();
        $last24 = now()->subDay();

        $completedToday = (float) $this->applyStatusFilter(
            $this->basePaymentsQuery($companyId, $branchId)
                ->whereDate('p.date_time', $today),
            'COMPLETADO'
        )->sum('s.total');

        $pendingTotal = (float) $this->applyStatusFilter(
            $this->basePaymentsQuery($companyId, $branchId),
            'PENDIENTE'
        )->sum('s.total');

        $transactionsLast24 = (int) $this->basePaymentsQuery($companyId, $branchId)
            ->where('p.date_time', '>=', $last24)
            ->count('p.payment_id');

        $commissionsToday = (float) $this->applyStatusFilter(
            $this->basePaymentsQuery($companyId, $branchId)
                ->whereDate('p.date_time', $today),
            'COMPLETADO'
        )->sum('p.commission');

        return response()->json([
            'completed_today' => $completedToday,
            'pending_total' => $pendingTotal,
            'transactions_last_24h' => $transactionsLast24,
            'commissions_today' => $commissionsToday,
        ]);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
            'search' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:all,COMPLETADO,PENDIENTE,CANCELADO'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $companyId = $this->getCompanyId();
        $branchId = $this->resolvePaymentsBranchId($request, $companyId);
        $search = trim($validated['search'] ?? '');
        $date = $validated['date'] ?? null;
        $status = $validated['status'] ?? 'all';
        $perPage = (int) ($validated['per_page'] ?? 15);

        $query = $this->basePaymentsQuery($companyId, $branchId)
            ->select([
                'p.payment_id',
                'p.date_time',
                'p.payment_method',
                'p.status_payment',
                'p.commission',
                'p.amount_paid',
                'p.change_given',
                'p.reference_payment',
                's.sale_id',
                's.total',
                'c.customer_id',
                'c.name_customer',
            ]);

        if ($date) {
            $query->whereDate('p.date_time', $date);
        }

        if ($status !== 'all') {
            $this->applyStatusFilter($query, $status);
        }

        if ($search !== '') {
            $digits = preg_replace('/\D/', '', $search);

            $query->where(function ($q) use ($search, $digits) {
                $q->where('c.name_customer', 'like', "%{$search}%")
                  ->orWhere('p.reference_payment', 'like', "%{$search}%");

                if ($digits !== '') {
                    $q->orWhere('p.payment_id', (int) $digits)
                      ->orWhere('s.sale_id', (int) $digits)
                      ->orWhereRaw("CONCAT('TXN-', LPAD(p.payment_id, 5, '0')) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("CONCAT('V-', LPAD(s.sale_id, 5, '0')) LIKE ?", ["%{$search}%"]);
                }
            });
        }

        $payments = $query
            ->orderByDesc('p.date_time')
            ->paginate($perPage);

        $payments->getCollection()->transform(function ($row) {
            return [
                'payment_id' => (int) $row->payment_id,
                'payment_code' => $this->formatPaymentCode((int) $row->payment_id),
                'date_time' => $row->date_time,
                'customer' => [
                    'customer_id' => (int) $row->customer_id,
                    'name_customer' => $row->name_customer,
                ],
                'concept' => 'Venta ' . $this->formatSaleFolio((int) $row->sale_id),
                'sale_id' => (int) $row->sale_id,
                'sale_folio' => $this->formatSaleFolio((int) $row->sale_id),
                'amount' => (float) $row->total,
                'payment_method' => $this->formatPaymentMethodLabel(
                    $row->payment_method,
                    $row->reference_payment
                ),
                'commission' => (float) $row->commission,
                'status' => $this->normalizePaymentStatus($row->status_payment),
            ];
        });

        return response()->json($payments);
    }

    public function show(Request $request, int $id)
    {
        $companyId = $this->getCompanyId();
        $branchId = $this->resolvePaymentsBranchId($request, $companyId);

        $payment = $this->basePaymentsQuery($companyId, $branchId)
            ->join('branch as b', 'b.branch_id', '=', 's.branch_idfk')
            ->join('userr as u', 'u.userr_id', '=', 's.cashier_userr_idfk')
            ->where('p.payment_id', $id)
            ->select([
                'p.payment_id',
                'p.date_time',
                'p.payment_method',
                'p.status_payment',
                'p.commission',
                'p.amount_paid',
                'p.change_given',
                'p.reference_payment',
                's.sale_id',
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
            ])
            ->first();

        if (!$payment) {
            throw ValidationException::withMessages([
                'payment_id' => ['El pago no existe en la sucursal actual.'],
            ]);
        }

        return response()->json([
            'payment_id' => (int) $payment->payment_id,
            'payment_code' => $this->formatPaymentCode((int) $payment->payment_id),
            'date_time' => $payment->date_time,
            'status' => $this->normalizePaymentStatus($payment->status_payment),
            'payment_method' => $this->formatPaymentMethodLabel(
                $payment->payment_method,
                $payment->reference_payment
            ),
            'reference_payment' => $payment->reference_payment,
            'commission' => (float) $payment->commission,
            'amount_paid' => (float) ($payment->amount_paid ?? 0),
            'change_given' => (float) ($payment->change_given ?? 0),
            'sale' => [
                'sale_id' => (int) $payment->sale_id,
                'sale_folio' => $this->formatSaleFolio((int) $payment->sale_id),
                'subtotal' => (float) $payment->subtotal,
                'discount' => (float) $payment->discount,
                'total' => (float) $payment->total,
                'status_sale' => $payment->status_sale,
            ],
            'customer' => [
                'customer_id' => (int) $payment->customer_id,
                'name_customer' => $payment->name_customer,
                'phone' => $payment->phone,
                'email' => $payment->email,
            ],
            'branch' => [
                'branch_id' => (int) $payment->branch_id,
                'name_branch' => $payment->name_branch,
            ],
            'cashier' => [
                'userr_id' => (int) $payment->userr_id,
                'name_user' => $payment->name_user,
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
            'search' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:all,COMPLETADO,PENDIENTE,CANCELADO'],
        ]);

        $companyId = $this->getCompanyId();
        $branchId = $this->resolvePaymentsBranchId($request, $companyId);
        $search = trim($validated['search'] ?? '');
        $date = $validated['date'] ?? null;
        $status = $validated['status'] ?? 'all';

        $query = $this->basePaymentsQuery($companyId, $branchId)
            ->select([
                'p.payment_id',
                'p.date_time',
                'p.payment_method',
                'p.status_payment',
                'p.commission',
                'p.reference_payment',
                's.sale_id',
                's.total',
                'c.name_customer',
            ]);

        if ($date) {
            $query->whereDate('p.date_time', $date);
        }

        if ($status !== 'all') {
            $this->applyStatusFilter($query, $status);
        }

        if ($search !== '') {
            $digits = preg_replace('/\D/', '', $search);

            $query->where(function ($q) use ($search, $digits) {
                $q->where('c.name_customer', 'like', "%{$search}%")
                  ->orWhere('p.reference_payment', 'like', "%{$search}%");

                if ($digits !== '') {
                    $q->orWhere('p.payment_id', (int) $digits)
                      ->orWhere('s.sale_id', (int) $digits)
                      ->orWhereRaw("CONCAT('TXN-', LPAD(p.payment_id, 5, '0')) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("CONCAT('V-', LPAD(s.sale_id, 5, '0')) LIKE ?", ["%{$search}%"]);
                }
            });
        }

        $rows = $query
            ->orderByDesc('p.date_time')
            ->get();

        $filename = 'pagos_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID TRANSACCION',
                'FECHA Y HORA',
                'CLIENTE',
                'CONCEPTO',
                'MONTO',
                'METODO DE PAGO',
                'COMISION',
                'ESTADO',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $this->formatPaymentCode((int) $row->payment_id),
                    $row->date_time,
                    $row->name_customer,
                    'Venta ' . $this->formatSaleFolio((int) $row->sale_id),
                    (float) $row->total,
                    $this->formatPaymentMethodLabel($row->payment_method, $row->reference_payment),
                    (float) $row->commission,
                    $this->normalizePaymentStatus($row->status_payment),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function basePaymentsQuery(int $companyId, int $branchId)
    {
        return DB::table('payments as p')
            ->join('sale as s', 's.sale_id', '=', 'p.sale_idfk')
            ->join('customer as c', 'c.customer_id', '=', 'p.customer_idfk')
            ->where('s.company_idfk', $companyId)
            ->where('s.branch_idfk', $branchId);
    }

    private function resolvePaymentsBranchId(Request $request, int $companyId): int
    {
        $branchId = $this->resolveBranchId(
            $request->filled('branch_id') ? (int) $request->branch_id : null,
            $companyId
        );

        if (!$branchId) {
            throw ValidationException::withMessages([
                'branch_id' => ['Debes tener una sucursal seleccionada.'],
            ]);
        }

        $this->getBranchOrFail($branchId, $companyId);

        return $branchId;
    }

    private function applyStatusFilter($query, string $status)
    {
        return match ($status) {
            'COMPLETADO' => $query->whereRaw(
                'UPPER(TRIM(p.status_payment)) IN (?, ?, ?)',
                ['PAGADO', 'APROBADO', 'COMPLETADO']
            ),
            'PENDIENTE' => $query->whereRaw(
                'UPPER(TRIM(p.status_payment)) IN (?, ?, ?)',
                ['PENDIENTE', 'EN_PROCESO', 'EN PROCESO']
            ),
            'CANCELADO' => $query->whereRaw(
                'UPPER(TRIM(p.status_payment)) IN (?, ?, ?, ?)',
                ['CANCELADO', 'CANCELADA', 'RECHAZADO', 'ANULADO']
            ),
            default => $query,
        };
    }

    private function normalizePaymentStatus(?string $status): string
    {
        $value = strtoupper(trim((string) $status));

        return match ($value) {
            'PAGADO', 'APROBADO', 'COMPLETADO' => 'COMPLETADO',
            'PENDIENTE', 'EN_PROCESO', 'EN PROCESO' => 'PENDIENTE',
            'CANCELADO', 'CANCELADA', 'RECHAZADO', 'ANULADO' => 'CANCELADO',
            default => $value !== '' ? $value : 'DESCONOCIDO',
        };
    }

    private function formatPaymentMethodLabel(?string $method, ?string $reference = null): string
    {
        $method = strtoupper(trim((string) $method));
        $reference = trim((string) $reference);

        $base = match ($method) {
            'EFECTIVO' => 'Efectivo',
            'TARJETA' => 'Tarjeta',
            'TRANSFERENCIA' => 'Transferencia',
            default => $method !== '' ? ucfirst(strtolower($method)) : 'Sin método',
        };

        if ($reference !== '' && in_array($method, ['TARJETA', 'TRANSFERENCIA'], true)) {
            return $base . ' · ' . $reference;
        }

        return $base;
    }

    private function formatPaymentCode(int $paymentId): string
    {
        return 'TXN-' . str_pad((string) $paymentId, 5, '0', STR_PAD_LEFT);
    }
}