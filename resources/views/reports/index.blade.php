@extends('layout.dashboard_design')

@section('title', 'Reportes')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/reports/index.css') }}">
@endpush

@section('content')
@php
    $reportsAccess = [
        'view' => \App\Support\UserAccess::has(auth()->user(), 'reports.view'),
        'export' => \App\Support\UserAccess::has(auth()->user(), 'reports.export'),
        'profit_view' => \App\Support\UserAccess::has(auth()->user(), 'reports.profit.view'),
    ];
@endphp

<div class="reports-wrap">
    <div class="reports-header">
        <div>
            <h1 style="font-size:32px; margin-bottom:8px;">Reportes</h1>
            <p class="text-muted">Analiza el rendimiento de tu negocio.</p>
        </div>

        <div class="reports-actions">
            <select id="reportsPeriod" class="btn">
               
                <option value="6m" selected>Últimos 6 meses</option>
                <option value="12m">Últimos 12 meses</option>
                <option value="30d">Últimos 30 días</option>
                <option value="90d">Últimos 90 días</option>
            </select>

            @if($reportsAccess['export'])
                <button type="button" class="btn" id="exportPdfButton">Exportar PDF</button>
            @endif
        </div>
    </div>

    @if($reportsAccess['profit_view'])
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-top">
                    <div>
                        <div class="summary-label">Ingresos totales</div>
                        <div class="summary-value" id="summaryIncome">$0</div>
                    </div>
                    <span class="change-badge change-neutral" id="summaryIncomeChange">0%</span>
                </div>
                <div class="summary-note" id="summaryIncomeNote">Últimos 6 meses</div>
            </div>

            <div class="summary-card">
                <div class="summary-top">
                    <div>
                        <div class="summary-label">Utilidad neta</div>
                        <div class="summary-value" id="summaryProfit">$0</div>
                    </div>
                    <span class="change-badge change-neutral" id="summaryProfitChange">0%</span>
                </div>
                <div class="summary-note">Después de costos</div>
            </div>

            <div class="summary-card">
                <div class="summary-top">
                    <div>
                        <div class="summary-label">Margen promedio</div>
                        <div class="summary-value" id="summaryMargin">0%</div>
                    </div>
                    <span class="change-badge change-neutral" id="summaryMarginChange">0%</span>
                </div>
                <div class="summary-note">De utilidad</div>
            </div>

            <div class="summary-card">
                <div class="summary-top">
                    <div>
                        <div class="summary-label">ROI</div>
                        <div class="summary-value" id="summaryRoi">0%</div>
                    </div>
                    <span class="change-badge change-neutral" id="summaryRoiChange">0%</span>
                </div>
                <div class="summary-note">Retorno de inversión</div>
            </div>
        </div>

        <div class="chart-grid-main">
            <div class="chart-card">
                <div class="chart-head">
                    <div class="chart-title">Ventas vs Costos</div>
                    <div class="chart-subtitle">Comparativa mensual en pesos</div>
                </div>
                <div class="chart-body">
                    <div class="bars-chart-wrap">
                        <div class="bars-grid" id="salesVsCostsChart"></div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <span class="legend-dot legend-sales"></span>
                                <span>Ventas</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot legend-costs"></span>
                                <span>Costos</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-head">
                    <div class="chart-title">Ventas por categoría</div>
                    <div class="chart-subtitle">Distribución porcentual</div>
                </div>
                <div class="chart-body">
                    <div class="donut-wrap">
                        <div class="donut-chart" id="categoryDonut">
                            <div class="donut-center">
                                <div class="donut-center-value" id="categoryDonutTotal">$0</div>
                                <div class="donut-center-label">Total</div>
                            </div>
                        </div>
                        <div class="category-legend" id="categoryLegend"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="chart-card">
        <div class="chart-head">
            <div class="chart-title">Horas pico de ventas</div>
            <div class="chart-subtitle">Número de transacciones por hora</div>
        </div>
        <div class="chart-body">
            <div class="line-chart-wrap">
                <svg class="line-chart-svg" id="peakHoursSvg" viewBox="0 0 760 260" preserveAspectRatio="none"></svg>
                <div class="line-labels" id="peakHoursLabels"></div>
            </div>
        </div>
    </div>

    <div class="chart-grid-secondary">
        <div class="list-card">
            <div class="chart-head">
                <div class="chart-title">Top productos vendidos</div>
                <div class="chart-subtitle">Productos con mayor facturación</div>
            </div>
            <div class="list-card-body" id="topProductsList"></div>
        </div>

        @if($reportsAccess['profit_view'])
            <div class="list-card">
                <div class="chart-head">
                    <div class="chart-title">Métodos de pago preferidos</div>
                    <div class="chart-subtitle">Distribución por monto cobrado</div>
                </div>
                <div class="list-card-body" id="paymentMethodsList"></div>
            </div>
        @endif
    </div>
</div>

<script>
    const reportsAccess = @json($reportsAccess);

    const reportsState = {
        period: '6m',
        categoryColors: ['#2340b3', '#0f172a', '#64748b', '#94a3b8', '#cbd5e1', '#818cf8']
    };

    function money(value) {
        return window.appFormat.money(value);
    }

    function percent(value) {
        return `${Number(value || 0).toFixed(1)}%`;
    }

    function badgeClass(value) {
        return Number(value || 0) > 0 ? 'change-positive' : 'change-neutral';
    }

    function signedPercent(value) {
        const num = Number(value || 0);
        return `${num > 0 ? '+' : ''}${num.toFixed(1)}%`;
    }

    async function apiFetch(url, options = {}) {
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.headers || {})
                }
            });

            const text = await response.text();
            let data = {};

            try {
                data = text ? JSON.parse(text) : {};
            } catch (e) {
                data = { message: text || 'Respuesta inválida.' };
            }

            return { response, data };
        } catch (error) {
            return {
                response: { ok: false, status: 0 },
                data: { message: error.message || 'Error de red.' }
            };
        }
    }

    function currentQuery() {
        return `period=${encodeURIComponent(reportsState.period)}`;
    }

    function setChangeBadge(id, value) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = signedPercent(value);
        el.className = `change-badge ${badgeClass(value)}`;
    }

    async function loadSummary() {
        if (!reportsAccess.profit_view) return;

        const { response, data } = await apiFetch(`/api/reports/summary?${currentQuery()}`);
        if (!response.ok) return;

        const income = document.getElementById('summaryIncome');
        const profit = document.getElementById('summaryProfit');
        const margin = document.getElementById('summaryMargin');
        const roi = document.getElementById('summaryRoi');
        const incomeNote = document.getElementById('summaryIncomeNote');

        if (income) income.textContent = money(data.income?.value ?? 0);
        if (profit) profit.textContent = money(data.profit?.value ?? 0);
        if (margin) margin.textContent = percent(data.margin?.value ?? 0);
        if (roi) roi.textContent = percent(data.roi?.value ?? 0);
        if (incomeNote) incomeNote.textContent = data.period?.label ?? 'Periodo';

        setChangeBadge('summaryIncomeChange', data.income?.change_percent ?? 0);
        setChangeBadge('summaryProfitChange', data.profit?.change_percent ?? 0);
        setChangeBadge('summaryMarginChange', data.margin?.change_percent ?? 0);
        setChangeBadge('summaryRoiChange', data.roi?.change_percent ?? 0);
    }

    async function loadSalesVsCosts() {
        if (!reportsAccess.profit_view) return;

        const { response, data } = await apiFetch(`/api/reports/sales-vs-costs?${currentQuery()}`);
        const container = document.getElementById('salesVsCostsChart');
        if (!container) return;

        if (!response.ok || !Array.isArray(data) || data.length === 0) {
            container.innerHTML = `<div class="empty-state">No hay datos para mostrar.</div>`;
            return;
        }

        const maxValue = Math.max(...data.flatMap(item => [Number(item.sales || 0), Number(item.costs || 0), 1]));
        const chartHeight = 220;

        container.innerHTML = data.map(item => {
            const salesHeight = Math.max((Number(item.sales || 0) / maxValue) * chartHeight, 2);
            const costsHeight = Math.max((Number(item.costs || 0) / maxValue) * chartHeight, 2);

            return `
                <div class="bar-group">
                    <div class="bar-stack">
                        <div class="bar bar-sales" style="height:${salesHeight}px;" title="Ventas: ${money(item.sales)}">
                            <span class="bar-value">${money(item.sales)}</span>
                        </div>
                        <div class="bar bar-costs" style="height:${costsHeight}px;" title="Costos: ${money(item.costs)}">
                            <span class="bar-value">${money(item.costs)}</span>
                        </div>
                    </div>
                    <div class="bar-label">${item.label}</div>
                </div>
            `;
        }).join('');
    }

    async function loadCategories() {
        if (!reportsAccess.profit_view) return;

        const { response, data } = await apiFetch(`/api/reports/categories?${currentQuery()}`);
        const donut = document.getElementById('categoryDonut');
        const legend = document.getElementById('categoryLegend');

        if (!donut || !legend) return;

        if (!response.ok || !Array.isArray(data.items) || data.items.length === 0) {
            donut.style.background = 'conic-gradient(#e2e8f0 0 100%)';
            const total = document.getElementById('categoryDonutTotal');
            if (total) total.textContent = money(0);
            legend.innerHTML = `<div class="empty-state">No hay categorías para mostrar.</div>`;
            return;
        }

        const total = document.getElementById('categoryDonutTotal');
        if (total) total.textContent = money(data.total_amount ?? 0);

        let accumulated = 0;
        const parts = data.items.map((item, index) => {
            const color = reportsState.categoryColors[index % reportsState.categoryColors.length];
            const start = accumulated;
            accumulated += Number(item.percent || 0);
            return `${color} ${start}% ${accumulated}%`;
        });

        donut.style.background = `conic-gradient(${parts.join(', ')})`;

        legend.innerHTML = data.items.map((item, index) => {
            const color = reportsState.categoryColors[index % reportsState.categoryColors.length];
            return `
                <div class="category-row">
                    <div class="category-left">
                        <span class="legend-dot" style="background:${color}; border-radius:999px;"></span>
                        <span class="category-name">${item.category_name}</span>
                    </div>
                    <span class="category-percent">${Number(item.percent || 0).toFixed(0)}%</span>
                </div>
            `;
        }).join('');
    }

    async function loadPeakHours() {
        const { response, data } = await apiFetch(`/api/reports/peak-hours?${currentQuery()}`);
        const svg = document.getElementById('peakHoursSvg');
        const labels = document.getElementById('peakHoursLabels');

        if (!svg || !labels) return;

        if (!response.ok || !Array.isArray(data) || data.length === 0) {
            svg.innerHTML = '';
            labels.innerHTML = `<div class="empty-state">No hay datos para mostrar.</div>`;
            return;
        }

        const width = 760;
        const height = 260;
        const paddingX = 20;
        const paddingY = 20;
        const maxValue = Math.max(...data.map(item => Number(item.sales_count || 0)), 1);
        const stepX = (width - (paddingX * 2)) / Math.max(data.length - 1, 1);

        const points = data.map((item, index) => {
            const x = paddingX + (index * stepX);
            const y = height - paddingY - ((Number(item.sales_count || 0) / maxValue) * (height - (paddingY * 2)));
            return { x, y, value: Number(item.sales_count || 0), label: item.label };
        });

        const polyline = points.map(point => `${point.x},${point.y}`).join(' ');

        svg.innerHTML = `
            <polyline
                fill="none"
                stroke="#22c55e"
                stroke-width="4"
                points="${polyline}"
                stroke-linecap="round"
                stroke-linejoin="round"
            ></polyline>
            ${points.map(point => `
                <circle cx="${point.x}" cy="${point.y}" r="6" fill="#22c55e">
                    <title>${point.label} · ventas: ${point.value}</title>
                </circle>
            `).join('')}
        `;

        labels.innerHTML = data.map(item => `
            <div class="line-label">${item.label}</div>
        `).join('');
    }

    async function loadTopProducts() {
        const { response, data } = await apiFetch(`/api/reports/top-products?${currentQuery()}&limit=5`);
        const container = document.getElementById('topProductsList');

        if (!container) return;

        if (!response.ok || !Array.isArray(data) || data.length === 0) {
            container.innerHTML = `<div class="empty-state">No hay productos para mostrar.</div>`;
            return;
        }

        container.innerHTML = data.map(item => `
            <div class="product-row">
                <div>
                    <div class="product-name">${item.name_product}</div>
                    <div class="product-meta">${item.units_sold} unidades vendidas</div>
                </div>
                <div class="product-amount">${money(item.total_amount)}</div>
            </div>
        `).join('');
    }

    async function loadPaymentMethods() {
        if (!reportsAccess.profit_view) return;

        const { response, data } = await apiFetch(`/api/reports/payment-methods?${currentQuery()}`);
        const container = document.getElementById('paymentMethodsList');

        if (!container) return;

        if (!response.ok || !Array.isArray(data.items) || data.items.length === 0) {
            container.innerHTML = `<div class="empty-state">No hay métodos de pago para mostrar.</div>`;
            return;
        }

        container.innerHTML = data.items.map(item => `
            <div class="method-row">
                <div style="flex:1;">
                    <div class="method-name">${item.payment_method}</div>
                    <div class="method-bar-wrap">
                        <div class="method-bar" style="width:${Math.max(Number(item.percent || 0), 2)}%;"></div>
                    </div>
                    <div class="method-meta">${money(item.total_amount)}</div>
                </div>
                <div class="method-right">${Number(item.percent || 0).toFixed(0)}%</div>
            </div>
        `).join('');
    }

    async function loadReports() {
        const tasks = [
            loadPeakHours(),
            loadTopProducts()
        ];

        if (reportsAccess.profit_view) {
            tasks.push(
                loadSummary(),
                loadSalesVsCosts(),
                loadCategories(),
                loadPaymentMethods()
            );
        }

        await Promise.all(tasks);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const periodSelect = document.getElementById('reportsPeriod');
        const exportButton = document.getElementById('exportPdfButton');

        if (periodSelect) {
            periodSelect.addEventListener('change', () => {
                reportsState.period = periodSelect.value;
                loadReports();
            });
        }

        if (exportButton) {
            exportButton.addEventListener('click', () => {
                if (!reportsAccess.export) return;
                window.print();
            });
        }

        if (!reportsAccess.view) return;

        loadReports();
    });
</script>
@endsection