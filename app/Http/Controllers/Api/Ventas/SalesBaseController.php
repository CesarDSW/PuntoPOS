<?php

namespace App\Http\Controllers\Api\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesBaseController extends Controller
{
    protected const TAX_RATE = 0.16;

    protected function getAuthenticatedUser()
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

    protected function getCompanyId(): int
    {
        return (int) $this->getAuthenticatedUser()->company_idfk;
    }

    protected function getUserId(): int
    {
        return (int) $this->getAuthenticatedUser()->userr_id;
    }

    protected function getBranchOrFail(int $branchId, int $companyId)
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

    protected function resolveBranchId(?int $requestBranchId, int $companyId): ?int
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

    protected function getCustomerOrFail(int $customerId, int $companyId)
{
    $customer = DB::table('customer')
        ->where('customer_id', $customerId)
        ->where('company_idfk', $companyId)
        ->where('status_customer', 1)
        ->first();

    if (!$customer) {
        throw ValidationException::withMessages([
            'customer_id' => ['El cliente no existe, no pertenece a la empresa o fue eliminado.'],
        ]);
    }

    return $customer;
}

    protected function getOrCreateGenericCustomer(int $companyId): object
    {
        $customer = DB::table('customer')
            ->where('company_idfk', $companyId)
            ->whereRaw('LOWER(name_customer) = ?', ['cliente general'])
            ->orderBy('customer_id')
            ->first();

        if ($customer) {
            return $customer;
        }

        $customerId = DB::table('customer')->insertGetId([
            'name_customer' => 'Cliente general',
            'phone' => '0000000000',
            'email' => 'cliente.general.' . $companyId . '@punto.local',
            'company_idfk' => $companyId,
        ]);

        return (object) [
            'customer_id' => $customerId,
            'name_customer' => 'Cliente general',
            'phone' => '0000000000',
            'email' => 'cliente.general.' . $companyId . '@punto.local',
            'company_idfk' => $companyId,
        ];
    }

    protected function normalizePaymentMethod(string $value): string
    {
        $value = strtoupper(trim($value));

        return match ($value) {
            'EFECTIVO', 'CASH' => 'EFECTIVO',
            'TARJETA', 'CARD' => 'TARJETA',
            'TRANSFERENCIA', 'TRANSFER' => 'TRANSFERENCIA',
            default => throw ValidationException::withMessages([
                'payment_method' => ['El método de pago debe ser EFECTIVO, TARJETA o TRANSFERENCIA.'],
            ]),
        };
    }

    protected function formatSaleFolio(int $saleId): string
    {
        return 'V-' . str_pad((string) $saleId, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Caja única por empresa.
     */
    protected function getOpenCashSession(int $companyId)
    {
        return DB::table('cash_register_session')
            ->where('company_idfk', $companyId)
            ->where('status_cash', 'ABIERTA')
            ->orderByDesc('cash_session_id')
            ->first();
    }

    /**
     * Turno abierto del usuario actual en la sucursal actual.
     */
    protected function getOpenShift(int $companyId, int $branchId, int $userId)
    {
        return DB::table('work_shift')
            ->where('company_idfk', $companyId)
            ->where('branch_idfk', $branchId)
            ->where('userr_idfk', $userId)
            ->where('status_shift', 'ABIERTO')
            ->orderByDesc('shift_id')
            ->first();
    }

    /**
     * Cualquier turno abierto en toda la empresa.
     */
    protected function getAnyOpenShift(int $companyId)
    {
        return DB::table('work_shift')
            ->where('company_idfk', $companyId)
            ->where('status_shift', 'ABIERTO')
            ->orderByDesc('shift_id')
            ->first();
    }

    protected function ensureOpenCashSessionOrFail(int $companyId)
    {
        $cashSession = $this->getOpenCashSession($companyId);

        if (!$cashSession) {
            throw ValidationException::withMessages([
                'cash_session' => ['Debes abrir caja antes de realizar ventas.'],
            ]);
        }

        return $cashSession;
    }

    protected function ensureOpenShiftOrFail(int $companyId, int $branchId, int $userId)
    {
        $shift = $this->getOpenShift($companyId, $branchId, $userId);

        if (!$shift) {
            throw ValidationException::withMessages([
                'shift' => ['Debes iniciar turno antes de realizar ventas.'],
            ]);
        }

        return $shift;
    }
}