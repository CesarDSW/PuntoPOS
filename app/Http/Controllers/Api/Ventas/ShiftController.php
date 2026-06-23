<?php

namespace App\Http\Controllers\Api\Ventas;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShiftController extends SalesBaseController
{
    public function summary(Request $request)
    {
        $this->authorizeSalesPosUse();

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
        ]);

        $companyId = $this->getCompanyId();
        $userId = $this->getUserId();
        $user = $this->getAuthenticatedUser();

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

        $shift = $this->ensureOpenShiftOrFail($companyId, $branchId, $userId);

        $start = Carbon::parse($shift->started_at);
        $end = now();

        $salesBaseQuery = DB::table('sale as s')
            ->leftJoin('payments as p', 'p.sale_idfk', '=', 's.sale_id')
            ->where('s.company_idfk', $companyId)
            ->where('s.branch_idfk', $branchId)
            ->where('s.cashier_userr_idfk', $userId)
            ->whereBetween('s.date_time', [$shift->started_at, $end])
            ->where('s.status_sale', 'PAGADA');

        $totalSold = (float) (clone $salesBaseQuery)->sum('s.total');
        $salesCount = (int) (clone $salesBaseQuery)->count('s.sale_id');
        $avgTicket = $salesCount > 0 ? round($totalSold / $salesCount, 2) : 0.00;

        $methodsRaw = (clone $salesBaseQuery)
            ->select('p.payment_method', DB::raw('COUNT(DISTINCT s.sale_id) as total_sales'))
            ->groupBy('p.payment_method')
            ->pluck('total_sales', 'p.payment_method');

        $paymentMethods = [
            'TARJETA' => (int) ($methodsRaw['TARJETA'] ?? 0),
            'EFECTIVO' => (int) ($methodsRaw['EFECTIVO'] ?? 0),
            'TRANSFERENCIA' => (int) ($methodsRaw['TRANSFERENCIA'] ?? 0),
        ];

        return response()->json([
            'shift_id' => (int) $shift->shift_id,
            'user_name' => $user->name_user,
            'started_at' => $shift->started_at,
            'ended_at_preview' => $end->toDateTimeString(),
            'started_at_label' => $start->format('d/m/Y - h:i a'),
            'ended_at_label' => $end->format('d/m/Y - h:i a'),
            'start_time_label' => $start->format('h:i a'),
            'end_time_label' => $end->format('h:i a'),
            'duration_text' => $this->formatDurationText($start, $end),
            'shift_type' => $shift->shift_type,
            'total_sold' => round($totalSold, 2),
            'sales_count' => $salesCount,
            'avg_ticket' => $avgTicket,
            'payment_methods' => $paymentMethods,
        ]);
    }

    public function open(Request $request)
    {
        $this->authorizeSalesPosUse();

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
            'shift_type' => ['nullable', 'string', 'max:30'],
            'notes_shift' => ['nullable', 'string', 'max:255'],
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

        $cashSession = $this->ensureOpenCashSessionOrFail($companyId);

        $existingOpenShift = $this->getAnyOpenShift($companyId);

        if ($existingOpenShift) {
            if ((int) $existingOpenShift->userr_idfk === $userId) {
                throw ValidationException::withMessages([
                    'shift' => ['Ya tienes un turno abierto en el sistema.'],
                ]);
            }

            throw ValidationException::withMessages([
                'shift' => ['Ya existe un turno abierto por otro usuario. Debe cerrarse antes de iniciar uno nuevo.'],
            ]);
        }

        $shiftId = DB::table('work_shift')->insertGetId([
            'cash_session_idfk' => $cashSession->cash_session_id,
            'company_idfk' => $companyId,
            'branch_idfk' => $branchId,
            'userr_idfk' => $userId,
            'started_at' => now(),
            'ended_at' => null,
            'status_shift' => 'ABIERTO',
            'shift_type' => strtoupper(trim($validated['shift_type'] ?? 'CAJERO')),
            'notes_shift' => $validated['notes_shift'] ?? null,
        ]);

        return response()->json([
            'message' => 'Turno iniciado correctamente.',
            'shift_id' => $shiftId,
        ], 201);
    }

    public function close(Request $request)
    {
        $this->authorizeSalesPosUse();

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
            'notes_shift' => ['nullable', 'string', 'max:255'],
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

        $shift = $this->ensureOpenShiftOrFail($companyId, $branchId, $userId);

        $existingNotes = trim((string) ($shift->notes_shift ?? ''));
        $closingNotes = trim((string) ($validated['notes_shift'] ?? ''));

        $finalNotes = $existingNotes;
        if ($closingNotes !== '') {
            $finalNotes = $existingNotes !== ''
                ? $existingNotes . ' | Cierre: ' . $closingNotes
                : 'Cierre: ' . $closingNotes;
        }

        DB::table('work_shift')
            ->where('shift_id', $shift->shift_id)
            ->update([
                'ended_at' => now(),
                'status_shift' => 'CERRADO',
                'notes_shift' => $finalNotes !== '' ? $finalNotes : null,
            ]);

        return response()->json([
            'message' => 'Turno cerrado correctamente.',
            'shift_id' => (int) $shift->shift_id,
        ]);
    }

    protected function authorizeSalesPosUse(): void
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            abort(401, 'Usuario no autenticado.');
        }

        if (empty($user->company_idfk)) {
            abort(403, 'No tienes una empresa asignada.');
        }
    }

    private function formatDurationText(Carbon $start, Carbon $end): string
    {
        $totalMinutes = $start->diffInMinutes($end);

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        }

        if ($hours > 0) {
            return "{$hours}h";
        }

        return "{$minutes}m";
    }
}