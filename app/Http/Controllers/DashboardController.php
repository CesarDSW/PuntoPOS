<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Models\Company;

class DashboardController extends Controller
{
    /* Dashboard */
    // Función para mostrar el dashboard como pantalla principal
    public function showDashboard()
    {
        $user = auth()->user();
        $showOnboarding = false;
        $company = null;

        // Valores por defecto para no romper la vista si no hay empresa/sucursal
        $currentBranchName = 'Sin sucursal';
        $incomeToday = 0;
        $incomeChange = 0;
        $salesToday = 0;
        $salesChange = 0;
        $activeCustomers = 0;
        $activeCustomersChange = 0;
        $transactionsToday = 0;
        $transactionsChange = 0;

        $weeklySalesLabels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $weeklySalesData = [0, 0, 0, 0, 0, 0, 0];

        $paymentBreakdown = [
            'Tarjeta' => ['count' => 0, 'percentage' => 0],
            'Efectivo' => ['count' => 0, 'percentage' => 0],
            'Transferencia' => ['count' => 0, 'percentage' => 0],
        ];

        $paymentChartLabels = ['Tarjeta', 'Efectivo', 'Transferencia'];
        $paymentChartData = [0, 0, 0];
        $recentTransactions = collect();

        if ($user && $user->company_idfk) {
            $company = Company::find($user->company_idfk);

            if ($company && !$company->onboarding_completed) {
                $showOnboarding = true;
            }

            if ($company) {
                $companyId = (int) $company->company_id;

                $currentBranch = $this->resolveCurrentBranch($companyId);
                $currentBranchId = $currentBranch ? (int) $currentBranch->branch_id : null;
                $currentBranchName = $currentBranch?->name_branch ?? 'Sin sucursal';

                $now = Carbon::now();

                $todayStart = $now->copy()->startOfDay();
                $todayEnd = $now->copy()->endOfDay();

                $sameDayLastWeekStart = $todayStart->copy()->subWeek();
                $sameDayLastWeekEnd = $todayEnd->copy()->subWeek();

                $activeCurrentStart = $now->copy()->subDays(29)->startOfDay();
                $activeCurrentEnd = $todayEnd->copy();

                $activePreviousStart = $activeCurrentStart->copy()->subDays(30);
                $activePreviousEnd = $activeCurrentStart->copy()->subSecond();

                $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
                $weekEnd = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

                $salesBase = $this->salesBaseQuery($companyId, $currentBranchId);
                $paymentsBase = $this->paymentsBaseQuery($companyId, $currentBranchId);

                $incomeToday = (float) (clone $salesBase)
                    ->whereBetween('date_time', [$todayStart, $todayEnd])
                    ->sum('total');

                $incomeLastWeekSameDay = (float) (clone $salesBase)
                    ->whereBetween('date_time', [$sameDayLastWeekStart, $sameDayLastWeekEnd])
                    ->sum('total');

                $salesToday = (int) (clone $salesBase)
                    ->whereBetween('date_time', [$todayStart, $todayEnd])
                    ->count();

                $salesLastWeekSameDay = (int) (clone $salesBase)
                    ->whereBetween('date_time', [$sameDayLastWeekStart, $sameDayLastWeekEnd])
                    ->count();

                $activeCustomers = (int) DB::table('sale as s')
    ->join('customer as c', 'c.customer_id', '=', 's.customer_idfk')
    ->where('s.company_idfk', $companyId)
    ->where('s.status_sale', '<>', 'CANCELADA')
    ->where('c.status_customer', 1)
    ->when($currentBranchId, function ($query) use ($currentBranchId) {
        $query->where('s.branch_idfk', $currentBranchId);
    })
    ->whereBetween('s.date_time', [$activeCurrentStart, $activeCurrentEnd])
    ->distinct()
    ->count('s.customer_idfk');

$activeCustomersPrevious = (int) DB::table('sale as s')
    ->join('customer as c', 'c.customer_id', '=', 's.customer_idfk')
    ->where('s.company_idfk', $companyId)
    ->where('s.status_sale', '<>', 'CANCELADA')
    ->where('c.status_customer', 1)
    ->when($currentBranchId, function ($query) use ($currentBranchId) {
        $query->where('s.branch_idfk', $currentBranchId);
    })
    ->whereBetween('s.date_time', [$activePreviousStart, $activePreviousEnd])
    ->distinct()
    ->count('s.customer_idfk');
                $transactionsToday = (int) (clone $paymentsBase)
                    ->whereBetween('p.date_time', [$todayStart, $todayEnd])
                    ->count();

                $transactionsLastWeekSameDay = (int) (clone $paymentsBase)
                    ->whereBetween('p.date_time', [$sameDayLastWeekStart, $sameDayLastWeekEnd])
                    ->count();

                $incomeChange = $this->calculateChangePercentage($incomeToday, $incomeLastWeekSameDay);
                $salesChange = $this->calculateChangePercentage($salesToday, $salesLastWeekSameDay);
                $activeCustomersChange = $this->calculateChangePercentage($activeCustomers, $activeCustomersPrevious);
                $transactionsChange = $this->calculateChangePercentage($transactionsToday, $transactionsLastWeekSameDay);

                $weeklySalesRaw = (clone $salesBase)
                    ->whereBetween('date_time', [$weekStart, $weekEnd])
                    ->selectRaw('DATE(date_time) as sale_date, COALESCE(SUM(total), 0) as total_amount')
                    ->groupBy('sale_date')
                    ->orderBy('sale_date')
                    ->get()
                    ->keyBy('sale_date');

                $weeklySalesLabels = [];
                $weeklySalesData = [];

                $daysMap = [
                    1 => 'Lun',
                    2 => 'Mar',
                    3 => 'Mié',
                    4 => 'Jue',
                    5 => 'Vie',
                    6 => 'Sáb',
                    7 => 'Dom',
                ];

                for ($i = 0; $i < 7; $i++) {
                    $day = $weekStart->copy()->addDays($i);
                    $dateKey = $day->format('Y-m-d');

                    $weeklySalesLabels[] = $daysMap[$day->dayOfWeekIso];
                    $weeklySalesData[] = (float) ($weeklySalesRaw[$dateKey]->total_amount ?? 0);
                }

                $paymentMethodsRaw = (clone $paymentsBase)
                    ->whereBetween('p.date_time', [$weekStart, $weekEnd])
                    ->selectRaw("
                        CASE
                            WHEN UPPER(p.payment_method) LIKE '%EFECTIVO%' THEN 'Efectivo'
                            WHEN UPPER(p.payment_method) LIKE '%TRANSFER%' THEN 'Transferencia'
                            WHEN UPPER(p.payment_method) LIKE '%TARJETA%' OR UPPER(p.payment_method) LIKE '%TERMINAL%' THEN 'Tarjeta'
                            ELSE 'Otro'
                        END as method_group,
                        COUNT(*) as total_count
                    ")
                    ->groupBy('method_group')
                    ->pluck('total_count', 'method_group');

                $paymentBreakdown = [
                    'Tarjeta' => [
                        'count' => (int) ($paymentMethodsRaw['Tarjeta'] ?? 0),
                        'percentage' => 0,
                    ],
                    'Efectivo' => [
                        'count' => (int) ($paymentMethodsRaw['Efectivo'] ?? 0),
                        'percentage' => 0,
                    ],
                    'Transferencia' => [
                        'count' => (int) ($paymentMethodsRaw['Transferencia'] ?? 0),
                        'percentage' => 0,
                    ],
                ];

                $paymentTotal = array_sum(array_column($paymentBreakdown, 'count'));

                foreach ($paymentBreakdown as $label => $info) {
                    $paymentBreakdown[$label]['percentage'] = $paymentTotal > 0
                        ? (int) round(($info['count'] / $paymentTotal) * 100)
                        : 0;
                }

                $paymentChartLabels = array_keys($paymentBreakdown);
                $paymentChartData = array_values(array_map(function ($item) {
                    return $item['count'];
                }, $paymentBreakdown));

                $recentTransactions = (clone $paymentsBase)
    ->join('customer as c', 'c.customer_id', '=', 'p.customer_idfk')
    ->where('c.status_customer', 1)
    ->select(
        'c.name_customer',
        'p.payment_method',
        'p.status_payment',
        'p.date_time'
    )
    ->selectRaw('COALESCE(NULLIF(p.amount_paid, 0), s.total) as display_amount')
    ->orderByDesc('p.date_time')
    ->limit(5)
    ->get()
    ->map(function ($transaction) {
        $transaction->hour_display = Carbon::parse($transaction->date_time)->format('H:i');
        return $transaction;
    });
            }
        }

        return view('dashboard', compact(
            'showOnboarding',
            'company',
            'currentBranchName',
            'incomeToday',
            'incomeChange',
            'salesToday',
            'salesChange',
            'activeCustomers',
            'activeCustomersChange',
            'transactionsToday',
            'transactionsChange',
            'weeklySalesLabels',
            'weeklySalesData',
            'paymentBreakdown',
            'paymentChartLabels',
            'paymentChartData',
            'recentTransactions'
        ));
    }

    // Función para guardar los datos del onboarding
    public function storeOnboarding(Request $request)
    {
        $user = Auth::user();
        $company = Company::findOrFail($user->company_idfk);

        if ($request->has('skip')) {
            $company->update([
                'onboarding_completed' => 1,
            ]);

            return redirect()->route('dashboard');
        }

        $request->validate([
            'address' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:20',
            'opening_time' => 'nullable',
            'closing_time' => 'nullable',
            'payment_methods' => 'nullable|array',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $logoPath = $company->logo;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $company->update([
            'address' => $request->address,
            'currency' => $request->currency,
            'opening_time' => $request->opening_time,
            'closing_time' => $request->closing_time,
            'logo' => $logoPath,
            'payment_methods' => $request->payment_methods ? json_encode($request->payment_methods) : null,
            'onboarding_completed' => 1,
        ]);

        return redirect()->route('dashboard')->with('success', 'Configuración inicial guardada.');
    }

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

    private function resolveCurrentBranch(int $companyId)
    {
        $sessionBranchId = session('current_branch_id');

        if ($sessionBranchId) {
            $branch = DB::table('branch')
                ->where('branch_id', $sessionBranchId)
                ->where('company_idfk', $companyId)
                ->first();

            if ($branch) {
                return $branch;
            }
        }

        $firstBranch = DB::table('branch')
            ->where('company_idfk', $companyId)
            ->orderBy('branch_id')
            ->first();

        if ($firstBranch) {
            session(['current_branch_id' => (int) $firstBranch->branch_id]);
            return $firstBranch;
        }

        session()->forget('current_branch_id');
        return null;
    }

    private function salesBaseQuery(int $companyId, ?int $branchId = null)
    {
        $query = DB::table('sale')
            ->where('company_idfk', $companyId)
            ->where('status_sale', '<>', 'CANCELADA');

        if ($branchId) {
            $query->where('branch_idfk', $branchId);
        }

        return $query;
    }

    private function paymentsBaseQuery(int $companyId, ?int $branchId = null)
    {
        $query = DB::table('payments as p')
            ->join('sale as s', 's.sale_id', '=', 'p.sale_idfk')
            ->where('s.company_idfk', $companyId)
            ->where('s.status_sale', '<>', 'CANCELADA');

        if ($branchId) {
            $query->where('s.branch_idfk', $branchId);
        }

        return $query;
    }

    private function calculateChangePercentage($current, $previous): float
    {
        $current = (float) $current;
        $previous = (float) $previous;

        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}