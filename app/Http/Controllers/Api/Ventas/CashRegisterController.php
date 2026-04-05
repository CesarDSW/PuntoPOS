<?php

namespace App\Http\Controllers\Api\Ventas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashRegisterController extends SalesBaseController
{
    public function open(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
            'opening_amount' => ['required', 'numeric', 'min:0'],
            'notes_opening' => ['nullable', 'string', 'max:255'],
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

        $existingCashSession = $this->getOpenCashSession($companyId);

        if ($existingCashSession) {
            throw ValidationException::withMessages([
                'cash_session' => ['Ya existe una caja abierta en el sistema.'],
            ]);
        }

        $cashSessionId = DB::table('cash_register_session')->insertGetId([
            'company_idfk' => $companyId,
            'branch_idfk' => $branchId,
            'opened_by_userr_idfk' => $userId,
            'closed_by_userr_idfk' => null,
            'opened_at' => now(),
            'closed_at' => null,
            'opening_amount' => (float) $validated['opening_amount'],
            'closing_amount' => null,
            'notes_opening' => $validated['notes_opening'] ?? null,
            'notes_closing' => null,
            'status_cash' => 'ABIERTA',
        ]);

        return response()->json([
            'message' => 'Caja abierta correctamente.',
            'cash_session_id' => $cashSessionId,
        ], 201);
    }

    public function close(Request $request)
    {
        $validated = $request->validate([
            'closing_amount' => ['required', 'numeric', 'min:0'],
            'notes_closing' => ['nullable', 'string', 'max:255'],
        ]);

        $companyId = $this->getCompanyId();
        $userId = $this->getUserId();

        $cashSession = $this->ensureOpenCashSessionOrFail($companyId);
        $openShift = $this->getAnyOpenShift($companyId);

        $closedAt = now();
        $autoClosedShiftId = null;
        $message = 'Caja cerrada correctamente.';

        DB::transaction(function () use (
            $validated,
            $userId,
            $cashSession,
            $openShift,
            $closedAt,
            &$autoClosedShiftId,
            &$message
        ) {
            if ($openShift) {
                if ((int) $openShift->userr_idfk !== $userId) {
                    throw ValidationException::withMessages([
                        'shift' => ['No puedes cerrar la caja porque el turno abierto pertenece a otro usuario.'],
                    ]);
                }

                $existingNotes = trim((string) ($openShift->notes_shift ?? ''));
                $autoCloseNote = 'Cierre automático por cierre de caja';

                $finalNotes = $existingNotes !== ''
                    ? $existingNotes . ' | ' . $autoCloseNote
                    : $autoCloseNote;

                DB::table('work_shift')
                    ->where('shift_id', $openShift->shift_id)
                    ->update([
                        'ended_at' => $closedAt,
                        'status_shift' => 'CERRADO',
                        'notes_shift' => $finalNotes,
                    ]);

                $autoClosedShiftId = (int) $openShift->shift_id;
                $message = 'Turno y caja cerrados correctamente.';
            }

            DB::table('cash_register_session')
                ->where('cash_session_id', $cashSession->cash_session_id)
                ->update([
                    'closed_by_userr_idfk' => $userId,
                    'closed_at' => $closedAt,
                    'closing_amount' => (float) $validated['closing_amount'],
                    'notes_closing' => $validated['notes_closing'] ?? null,
                    'status_cash' => 'CERRADA',
                ]);
        });

        return response()->json([
            'message' => $message,
            'cash_session_id' => (int) $cashSession->cash_session_id,
            'auto_closed_shift_id' => $autoClosedShiftId,
            'redirect_url' => url('/ventas/cajas/' . $cashSession->cash_session_id),
        ]);
    }

    public function history(Request $request)
    {
        $companyId = $this->getCompanyId();

        $validated = $request->validate([
            'status' => ['nullable', 'in:all,ABIERTA,CERRADA'],
        ]);

        $status = $validated['status'] ?? 'all';

        $query = DB::table('cash_register_session as cs')
            ->join('branch as b', 'b.branch_id', '=', 'cs.branch_idfk')
            ->join('userr as uo', 'uo.userr_id', '=', 'cs.opened_by_userr_idfk')
            ->leftJoin('userr as uc', 'uc.userr_id', '=', 'cs.closed_by_userr_idfk')
            ->where('cs.company_idfk', $companyId)
            ->select([
                'cs.cash_session_id',
                'cs.branch_idfk',
                'b.name_branch',
                'cs.opened_at',
                'cs.closed_at',
                'cs.opening_amount',
                'cs.closing_amount',
                'cs.notes_opening',
                'cs.notes_closing',
                'cs.status_cash',
                'uo.name_user as opened_by_name',
                'uc.name_user as closed_by_name',
            ])
            ->orderByDesc('cs.cash_session_id');

        if ($status !== 'all') {
            $query->where('cs.status_cash', $status);
        }

        $sessions = $query->get()->map(function ($session) use ($companyId) {
            $shiftBase = DB::table('work_shift')
                ->where('cash_session_idfk', $session->cash_session_id);

            $shiftsCount = (clone $shiftBase)->count();
            $cashiersCount = (clone $shiftBase)->distinct()->count('userr_idfk');

            $summary = $this->buildCashSessionSummary(
                $companyId,
                $session->opened_at,
                $session->closed_at
            );

            return [
                'cash_session_id' => (int) $session->cash_session_id,
                'branch' => [
                    'branch_id' => (int) $session->branch_idfk,
                    'name_branch' => $session->name_branch,
                ],
                'opened_at' => $session->opened_at,
                'closed_at' => $session->closed_at,
                'opening_amount' => (float) $session->opening_amount,
                'closing_amount' => $session->closing_amount !== null ? (float) $session->closing_amount : null,
                'difference_amount' => $session->closing_amount !== null
                    ? round((float) $session->closing_amount - (float) $session->opening_amount, 2)
                    : null,
                'notes_opening' => $session->notes_opening,
                'notes_closing' => $session->notes_closing,
                'status_cash' => $session->status_cash,
                'opened_by_name' => $session->opened_by_name,
                'closed_by_name' => $session->closed_by_name,
                'shifts_count' => (int) $shiftsCount,
                'cashiers_count' => (int) $cashiersCount,
                'sales_count' => $summary['sales_count'],
                'total_sold' => $summary['total_sold'],
                'avg_ticket' => $summary['avg_ticket'],
                'payment_methods' => $summary['payment_methods'],
            ];
        });

        return response()->json($sessions);
    }

    public function show(int $id)
    {
        $companyId = $this->getCompanyId();

        $session = DB::table('cash_register_session as cs')
            ->join('branch as b', 'b.branch_id', '=', 'cs.branch_idfk')
            ->join('userr as uo', 'uo.userr_id', '=', 'cs.opened_by_userr_idfk')
            ->leftJoin('userr as uc', 'uc.userr_id', '=', 'cs.closed_by_userr_idfk')
            ->where('cs.company_idfk', $companyId)
            ->where('cs.cash_session_id', $id)
            ->select([
                'cs.cash_session_id',
                'cs.company_idfk',
                'cs.branch_idfk',
                'b.name_branch',
                'cs.opened_at',
                'cs.closed_at',
                'cs.opening_amount',
                'cs.closing_amount',
                'cs.notes_opening',
                'cs.notes_closing',
                'cs.status_cash',
                'uo.userr_id as opened_by_id',
                'uo.name_user as opened_by_name',
                'uc.userr_id as closed_by_id',
                'uc.name_user as closed_by_name',
            ])
            ->first();

        if (!$session) {
            throw ValidationException::withMessages([
                'cash_session_id' => ['La caja no existe o no pertenece a tu empresa.'],
            ]);
        }

        $sessionSummary = $this->buildCashSessionSummary(
            $companyId,
            $session->opened_at,
            $session->closed_at
        );

        $shifts = DB::table('work_shift as ws')
            ->join('userr as u', 'u.userr_id', '=', 'ws.userr_idfk')
            ->join('branch as b', 'b.branch_id', '=', 'ws.branch_idfk')
            ->where('ws.cash_session_idfk', $session->cash_session_id)
            ->select([
                'ws.shift_id',
                'ws.company_idfk',
                'ws.branch_idfk',
                'b.name_branch',
                'ws.userr_idfk',
                'u.name_user',
                'ws.started_at',
                'ws.ended_at',
                'ws.status_shift',
                'ws.shift_type',
                'ws.notes_shift',
            ])
            ->orderBy('ws.started_at')
            ->get()
            ->map(function ($shift) use ($companyId, $session) {
                $shiftSummary = $this->buildShiftSummary(
                    $companyId,
                    (int) $shift->branch_idfk,
                    (int) $shift->userr_idfk,
                    $shift->started_at,
                    $shift->ended_at ?? $session->closed_at
                );

                return [
                    'shift_id' => (int) $shift->shift_id,
                    'user' => [
                        'userr_id' => (int) $shift->userr_idfk,
                        'name_user' => $shift->name_user,
                    ],
                    'branch' => [
                        'branch_id' => (int) $shift->branch_idfk,
                        'name_branch' => $shift->name_branch,
                    ],
                    'started_at' => $shift->started_at,
                    'ended_at' => $shift->ended_at,
                    'status_shift' => $shift->status_shift,
                    'shift_type' => $shift->shift_type,
                    'notes_shift' => $shift->notes_shift,
                    'sales_count' => $shiftSummary['sales_count'],
                    'total_sold' => $shiftSummary['total_sold'],
                    'avg_ticket' => $shiftSummary['avg_ticket'],
                    'payment_methods' => $shiftSummary['payment_methods'],
                ];
            })
            ->values();

        return response()->json([
            'cash_session_id' => (int) $session->cash_session_id,
            'status_cash' => $session->status_cash,
            'branch' => [
                'branch_id' => (int) $session->branch_idfk,
                'name_branch' => $session->name_branch,
            ],
            'opened_at' => $session->opened_at,
            'closed_at' => $session->closed_at,
            'opening_amount' => (float) $session->opening_amount,
            'closing_amount' => $session->closing_amount !== null ? (float) $session->closing_amount : null,
            'difference_amount' => $session->closing_amount !== null
                ? round((float) $session->closing_amount - (float) $session->opening_amount, 2)
                : null,
            'notes_opening' => $session->notes_opening,
            'notes_closing' => $session->notes_closing,
            'opened_by' => [
                'userr_id' => (int) $session->opened_by_id,
                'name_user' => $session->opened_by_name,
            ],
            'closed_by' => $session->closed_by_id
                ? [
                    'userr_id' => (int) $session->closed_by_id,
                    'name_user' => $session->closed_by_name,
                ]
                : null,
            'summary' => $sessionSummary,
            'shifts' => $shifts,
        ]);
    }

    private function buildCashSessionSummary(int $companyId, string $openedAt, ?string $closedAt): array
    {
        $endAt = $closedAt ?? now()->toDateTimeString();

        $salesBase = DB::table('sale as s')
            ->leftJoin('payments as p', 'p.sale_idfk', '=', 's.sale_id')
            ->where('s.company_idfk', $companyId)
            ->where('s.status_sale', 'PAGADA')
            ->whereBetween('s.date_time', [$openedAt, $endAt]);

        $salesCount = (int) (clone $salesBase)->count('s.sale_id');
        $totalSold = round((float) (clone $salesBase)->sum('s.total'), 2);
        $avgTicket = $salesCount > 0 ? round($totalSold / $salesCount, 2) : 0.00;

        $methodsRaw = (clone $salesBase)
            ->select(
                'p.payment_method',
                DB::raw('COUNT(DISTINCT s.sale_id) as total_sales'),
                DB::raw('COALESCE(SUM(s.total), 0) as total_amount')
            )
            ->groupBy('p.payment_method')
            ->get()
            ->keyBy('payment_method');

        return [
            'sales_count' => $salesCount,
            'total_sold' => $totalSold,
            'avg_ticket' => $avgTicket,
            'payment_methods' => [
                'EFECTIVO' => [
                    'sales_count' => (int) (($methodsRaw->get('EFECTIVO')->total_sales) ?? 0),
                    'total_amount' => (float) (($methodsRaw->get('EFECTIVO')->total_amount) ?? 0),
                ],
                'TARJETA' => [
                    'sales_count' => (int) (($methodsRaw->get('TARJETA')->total_sales) ?? 0),
                    'total_amount' => (float) (($methodsRaw->get('TARJETA')->total_amount) ?? 0),
                ],
                'TRANSFERENCIA' => [
                    'sales_count' => (int) (($methodsRaw->get('TRANSFERENCIA')->total_sales) ?? 0),
                    'total_amount' => (float) (($methodsRaw->get('TRANSFERENCIA')->total_amount) ?? 0),
                ],
            ],
        ];
    }

    private function buildShiftSummary(
        int $companyId,
        int $branchId,
        int $userId,
        string $startedAt,
        ?string $endedAt
    ): array {
        $endAt = $endedAt ?? now()->toDateTimeString();

        $salesBase = DB::table('sale as s')
            ->leftJoin('payments as p', 'p.sale_idfk', '=', 's.sale_id')
            ->where('s.company_idfk', $companyId)
            ->where('s.branch_idfk', $branchId)
            ->where('s.cashier_userr_idfk', $userId)
            ->where('s.status_sale', 'PAGADA')
            ->whereBetween('s.date_time', [$startedAt, $endAt]);

        $salesCount = (int) (clone $salesBase)->count('s.sale_id');
        $totalSold = round((float) (clone $salesBase)->sum('s.total'), 2);
        $avgTicket = $salesCount > 0 ? round($totalSold / $salesCount, 2) : 0.00;

        $methodsRaw = (clone $salesBase)
            ->select(
                'p.payment_method',
                DB::raw('COUNT(DISTINCT s.sale_id) as total_sales'),
                DB::raw('COALESCE(SUM(s.total), 0) as total_amount')
            )
            ->groupBy('p.payment_method')
            ->get()
            ->keyBy('payment_method');

        return [
            'sales_count' => $salesCount,
            'total_sold' => $totalSold,
            'avg_ticket' => $avgTicket,
            'payment_methods' => [
                'EFECTIVO' => [
                    'sales_count' => (int) (($methodsRaw->get('EFECTIVO')->total_sales) ?? 0),
                    'total_amount' => (float) (($methodsRaw->get('EFECTIVO')->total_amount) ?? 0),
                ],
                'TARJETA' => [
                    'sales_count' => (int) (($methodsRaw->get('TARJETA')->total_sales) ?? 0),
                    'total_amount' => (float) (($methodsRaw->get('TARJETA')->total_amount) ?? 0),
                ],
                'TRANSFERENCIA' => [
                    'sales_count' => (int) (($methodsRaw->get('TRANSFERENCIA')->total_sales) ?? 0),
                    'total_amount' => (float) (($methodsRaw->get('TRANSFERENCIA')->total_amount) ?? 0),
                ],
            ],
        ];
    }
}