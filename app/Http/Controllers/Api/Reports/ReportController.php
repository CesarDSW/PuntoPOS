<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Api\Ventas\SalesBaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportController extends SalesBaseController
{
    public function summary(Request $request)
    {
        $companyId = $this->getCompanyId();
        $branchId = $this->resolveReportBranchId($request, $companyId);
        $period = $this->resolvePeriod($request->query('period', '6m'));

        $current = $this->calculateCoreMetrics($companyId, $branchId, $period['from'], $period['to']);
        $previous = $this->calculateCoreMetrics($companyId, $branchId, $period['previous_from'], $period['previous_to']);

        return response()->json([
            'period' => [
                'key' => $period['key'],
                'label' => $period['label'],
                'from' => $period['from']->toDateTimeString(),
                'to' => $period['to']->toDateTimeString(),
            ],
            'income' => [
                'value' => $current['revenue'],
                'change_percent' => $this->percentChange($current['revenue'], $previous['revenue']),
            ],
            'profit' => [
                'value' => $current['profit'],
                'change_percent' => $this->percentChange($current['profit'], $previous['profit']),
            ],
            'margin' => [
                'value' => $current['margin_percent'],
                'change_percent' => $this->percentChange($current['margin_percent'], $previous['margin_percent']),
            ],
            'roi' => [
                'value' => $current['roi_percent'],
                'change_percent' => $this->percentChange($current['roi_percent'], $previous['roi_percent']),
            ],
        ]);
    }

    public function salesVsCosts(Request $request)
    {
        $companyId = $this->getCompanyId();
        $branchId = $this->resolveReportBranchId($request, $companyId);
        $period = $this->resolvePeriod($request->query('period', '6m'));

        $months = $this->buildMonthBuckets($period['months_count']);

        $salesRows = DB::table('sale as s')
            ->where('s.company_idfk', $companyId)
            ->where('s.branch_idfk', $branchId)
            ->where('s.status_sale', 'PAGADA')
            ->whereBetween('s.date_time', [
                $months[0]['start']->toDateTimeString(),
                end($months)['end']->toDateTimeString(),
            ])
            ->selectRaw("DATE_FORMAT(s.date_time, '%Y-%m') as ym, COALESCE(SUM(s.total), 0) as total_sales")
            ->groupByRaw("DATE_FORMAT(s.date_time, '%Y-%m')")
            ->get()
            ->keyBy('ym');

        $costRows = DB::table('saleitem as si')
            ->join('sale as s', 's.sale_id', '=', 'si.sale_idfk')
            ->leftJoin('productt as p', 'p.product_id', '=', 'si.product_idfk')
            ->where('s.company_idfk', $companyId)
            ->where('s.branch_idfk', $branchId)
            ->where('s.status_sale', 'PAGADA')
            ->whereBetween('s.date_time', [
                $months[0]['start']->toDateTimeString(),
                end($months)['end']->toDateTimeString(),
            ])
            ->selectRaw("
                DATE_FORMAT(s.date_time, '%Y-%m') as ym,
                COALESCE(SUM(
                    CASE
                        WHEN si.item_type = 'PRODUCTO' THEN COALESCE(p.cost, 0) * si.amount
                        ELSE 0
                    END
                ), 0) as total_costs
            ")
            ->groupByRaw("DATE_FORMAT(s.date_time, '%Y-%m')")
            ->get()
            ->keyBy('ym');

        $items = collect($months)->map(function ($month) use ($salesRows, $costRows) {
            $ym = $month['ym'];
            $sales = (float) ($salesRows[$ym]->total_sales ?? 0);
            $costs = (float) ($costRows[$ym]->total_costs ?? 0);

            return [
                'label' => $month['label'],
                'year_month' => $ym,
                'sales' => round($sales, 2),
                'costs' => round($costs, 2),
                'profit' => round($sales - $costs, 2),
            ];
        })->values();

        return response()->json($items);
    }

    public function categories(Request $request)
    {
        $companyId = $this->getCompanyId();
        $branchId = $this->resolveReportBranchId($request, $companyId);
        $period = $this->resolvePeriod($request->query('period', '6m'));

        $rows = DB::table('saleitem as si')
            ->join('sale as s', 's.sale_id', '=', 'si.sale_idfk')
            ->leftJoin('productt as p', 'p.product_id', '=', 'si.product_idfk')
            ->leftJoin('servicee as sv', 'sv.service_id', '=', 'si.service_idfk')
            ->leftJoin('category as cp', 'cp.category_id', '=', 'p.category_idfk')
            ->leftJoin('category as cs', 'cs.category_id', '=', 'sv.category_idfk')
            ->where('s.company_idfk', $companyId)
            ->where('s.branch_idfk', $branchId)
            ->where('s.status_sale', 'PAGADA')
            ->whereBetween('s.date_time', [
                $period['from']->toDateTimeString(),
                $period['to']->toDateTimeString(),
            ])
            ->selectRaw("
                COALESCE(
                    CASE
                        WHEN si.item_type = 'PRODUCTO' THEN cp.name_category
                        ELSE cs.name_category
                    END,
                    'Sin categoría'
                ) as category_name,
                COALESCE(SUM(si.total_line), 0) as total_amount
            ")
            ->groupByRaw("
                COALESCE(
                    CASE
                        WHEN si.item_type = 'PRODUCTO' THEN cp.name_category
                        ELSE cs.name_category
                    END,
                    'Sin categoría'
                )
            ")
            ->orderByDesc('total_amount')
            ->get();

        $grandTotal = (float) $rows->sum('total_amount');

        $items = $rows->map(function ($row) use ($grandTotal) {
            $amount = (float) $row->total_amount;
            $percent = $grandTotal > 0 ? round(($amount / $grandTotal) * 100, 2) : 0;

            return [
                'category_name' => $row->category_name,
                'total_amount' => round($amount, 2),
                'percent' => $percent,
            ];
        })->values();

        return response()->json([
            'total_amount' => round($grandTotal, 2),
            'items' => $items,
        ]);
    }

    public function peakHours(Request $request)
    {
        $companyId = $this->getCompanyId();
        $branchId = $this->resolveReportBranchId($request, $companyId);
        $period = $this->resolvePeriod($request->query('period', '6m'));

        $rows = DB::table('sale as s')
            ->where('s.company_idfk', $companyId)
            ->where('s.branch_idfk', $branchId)
            ->where('s.status_sale', 'PAGADA')
            ->whereBetween('s.date_time', [
                $period['from']->toDateTimeString(),
                $period['to']->toDateTimeString(),
            ])
            ->selectRaw("HOUR(s.date_time) as hour_num, COUNT(*) as sales_count")
            ->groupBy('hour_num')
            ->orderBy('hour_num')
            ->get();

        $rawMap = [];
        foreach ($rows as $row) {
            $rawMap[(int) $row->hour_num] = (int) $row->sales_count;
        }

        $buckets = [
            8 => 0,
            10 => 0,
            12 => 0,
            14 => 0,
            16 => 0,
            18 => 0,
            20 => 0,
        ];

        foreach ($rawMap as $hour => $count) {
            if ($hour >= 8 && $hour < 10) $buckets[8] += $count;
            elseif ($hour >= 10 && $hour < 12) $buckets[10] += $count;
            elseif ($hour >= 12 && $hour < 14) $buckets[12] += $count;
            elseif ($hour >= 14 && $hour < 16) $buckets[14] += $count;
            elseif ($hour >= 16 && $hour < 18) $buckets[16] += $count;
            elseif ($hour >= 18 && $hour < 20) $buckets[18] += $count;
            elseif ($hour >= 20 && $hour < 22) $buckets[20] += $count;
        }

        $items = collect($buckets)->map(function ($count, $hour) {
            return [
                'label' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':00',
                'sales_count' => (int) $count,
            ];
        })->values();

        return response()->json($items);
    }

    public function topProducts(Request $request)
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $companyId = $this->getCompanyId();
        $branchId = $this->resolveReportBranchId($request, $companyId);
        $period = $this->resolvePeriod($request->query('period', '6m'));
        $limit = (int) ($validated['limit'] ?? 5);

        $rows = DB::table('saleitem as si')
            ->join('sale as s', 's.sale_id', '=', 'si.sale_idfk')
            ->join('productt as p', 'p.product_id', '=', 'si.product_idfk')
            ->where('s.company_idfk', $companyId)
            ->where('s.branch_idfk', $branchId)
            ->where('s.status_sale', 'PAGADA')
            ->where('si.item_type', 'PRODUCTO')
            ->whereBetween('s.date_time', [
                $period['from']->toDateTimeString(),
                $period['to']->toDateTimeString(),
            ])
            ->selectRaw("
                p.product_id,
                p.name_product,
                COALESCE(SUM(si.amount), 0) as units_sold,
                COALESCE(SUM(si.total_line), 0) as total_amount
            ")
            ->groupBy('p.product_id', 'p.name_product')
            ->orderByDesc('total_amount')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                return [
                    'product_id' => (int) $row->product_id,
                    'name_product' => $row->name_product,
                    'units_sold' => (int) $row->units_sold,
                    'total_amount' => (float) $row->total_amount,
                ];
            })
            ->values();

        return response()->json($rows);
    }

    public function paymentMethods(Request $request)
    {
        $companyId = $this->getCompanyId();
        $branchId = $this->resolveReportBranchId($request, $companyId);
        $period = $this->resolvePeriod($request->query('period', '6m'));

        $rows = DB::table('payments as p')
            ->join('sale as s', 's.sale_id', '=', 'p.sale_idfk')
            ->where('s.company_idfk', $companyId)
            ->where('s.branch_idfk', $branchId)
            ->where('s.status_sale', 'PAGADA')
            ->whereBetween('p.date_time', [
                $period['from']->toDateTimeString(),
                $period['to']->toDateTimeString(),
            ])
            ->selectRaw("
                UPPER(TRIM(p.payment_method)) as payment_method,
                COALESCE(SUM(s.total), 0) as total_amount
            ")
            ->groupByRaw("UPPER(TRIM(p.payment_method))")
            ->orderByDesc('total_amount')
            ->get();

        $grandTotal = (float) $rows->sum('total_amount');

        $items = $rows->map(function ($row) use ($grandTotal) {
            $amount = (float) $row->total_amount;
            $percent = $grandTotal > 0 ? round(($amount / $grandTotal) * 100, 2) : 0;

            return [
                'payment_method' => $this->formatPaymentMethodLabel($row->payment_method),
                'total_amount' => round($amount, 2),
                'percent' => $percent,
            ];
        })->values();

        return response()->json([
            'total_amount' => round($grandTotal, 2),
            'items' => $items,
        ]);
    }

    private function resolveReportBranchId(Request $request, int $companyId): int
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

    private function resolvePeriod(string $value): array
    {
        $value = strtolower(trim($value));
        $now = now();

        return match ($value) {
            '30d' => [
                'key' => '30d',
                'label' => 'Últimos 30 días',
                'from' => $now->copy()->startOfDay()->subDays(29),
                'to' => $now->copy()->endOfDay(),
                'previous_from' => $now->copy()->startOfDay()->subDays(59),
                'previous_to' => $now->copy()->endOfDay()->subDays(30),
                'months_count' => 1,
            ],
            '90d' => [
                'key' => '90d',
                'label' => 'Últimos 90 días',
                'from' => $now->copy()->startOfDay()->subDays(89),
                'to' => $now->copy()->endOfDay(),
                'previous_from' => $now->copy()->startOfDay()->subDays(179),
                'previous_to' => $now->copy()->endOfDay()->subDays(90),
                'months_count' => 3,
            ],
            '12m' => [
                'key' => '12m',
                'label' => 'Últimos 12 meses',
                'from' => $now->copy()->startOfMonth()->subMonths(11),
                'to' => $now->copy()->endOfMonth(),
                'previous_from' => $now->copy()->startOfMonth()->subMonths(23),
                'previous_to' => $now->copy()->subMonths(12)->endOfMonth(),
                'months_count' => 12,
            ],
            default => [
                'key' => '6m',
                'label' => 'Últimos 6 meses',
                'from' => $now->copy()->startOfMonth()->subMonths(5),
                'to' => $now->copy()->endOfMonth(),
                'previous_from' => $now->copy()->startOfMonth()->subMonths(11),
                'previous_to' => $now->copy()->subMonths(6)->endOfMonth(),
                'months_count' => 6,
            ],
        };
    }

    private function calculateCoreMetrics(int $companyId, int $branchId, Carbon $from, Carbon $to): array
    {
        $revenue = (float) DB::table('sale as s')
            ->where('s.company_idfk', $companyId)
            ->where('s.branch_idfk', $branchId)
            ->where('s.status_sale', 'PAGADA')
            ->whereBetween('s.date_time', [$from->toDateTimeString(), $to->toDateTimeString()])
            ->sum('s.total');

        $cost = (float) DB::table('saleitem as si')
            ->join('sale as s', 's.sale_id', '=', 'si.sale_idfk')
            ->leftJoin('productt as p', 'p.product_id', '=', 'si.product_idfk')
            ->where('s.company_idfk', $companyId)
            ->where('s.branch_idfk', $branchId)
            ->where('s.status_sale', 'PAGADA')
            ->whereBetween('s.date_time', [$from->toDateTimeString(), $to->toDateTimeString()])
            ->selectRaw("
                COALESCE(SUM(
                    CASE
                        WHEN si.item_type = 'PRODUCTO' THEN COALESCE(p.cost, 0) * si.amount
                        ELSE 0
                    END
                ), 0) as total_cost
            ")
            ->value('total_cost');

        $profit = $revenue - $cost;
        $marginPercent = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.00;
        $roiPercent = $cost > 0 ? round(($profit / $cost) * 100, 2) : 0.00;

        return [
            'revenue' => round($revenue, 2),
            'cost' => round($cost, 2),
            'profit' => round($profit, 2),
            'margin_percent' => $marginPercent,
            'roi_percent' => $roiPercent,
        ];
    }

    private function percentChange(float $current, float $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function buildMonthBuckets(int $monthsCount): array
    {
        $start = now()->copy()->startOfMonth()->subMonths($monthsCount - 1);
        $items = [];

        for ($i = 0; $i < $monthsCount; $i++) {
            $month = $start->copy()->addMonths($i);

            $items[] = [
                'ym' => $month->format('Y-m'),
                'label' => $this->shortMonthLabel($month),
                'start' => $month->copy()->startOfMonth(),
                'end' => $month->copy()->endOfMonth(),
            ];
        }

        return $items;
    }

    private function shortMonthLabel(Carbon $date): string
    {
        return match ((int) $date->format('n')) {
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic',
        };
    }

    private function formatPaymentMethodLabel(?string $method): string
    {
        $method = strtoupper(trim((string) $method));

        return match ($method) {
            'EFECTIVO' => 'Efectivo',
            'TARJETA' => 'Tarjeta',
            'TRANSFERENCIA' => 'Transferencia',
            default => $method !== '' ? ucfirst(strtolower($method)) : 'Sin método',
        };
    }
}