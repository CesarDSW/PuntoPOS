@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
<style>
    .reports-wrap { display:flex; flex-direction:column; gap:18px; }

    .reports-header {
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:16px;
        flex-wrap:wrap;
    }

    .reports-actions {
        display:flex;
        gap:10px;
        flex-wrap:wrap;
    }

    .summary-grid {
        display:grid;
        grid-template-columns:repeat(4, minmax(0, 1fr));
        gap:16px;
    }

    .summary-card,
    .chart-card,
    .list-card {
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius:18px;
    }

    .summary-card {
        padding:22px;
    }

    .summary-top {
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:12px;
        margin-bottom:12px;
    }

    .summary-label {
        color:#64748b;
        font-size:14px;
        margin-bottom:8px;
    }

    .summary-value {
        font-size:22px;
        font-weight:700;
    }

    .summary-note {
        color:#64748b;
        font-size:13px;
        margin-top:8px;
    }

    .change-badge {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:6px 10px;
        border-radius:999px;
        font-size:12px;
        font-weight:700;
        white-space:nowrap;
    }

    .change-positive {
        background:#dcfce7;
        color:#166534;
    }

    .change-neutral {
        background:#e2e8f0;
        color:#334155;
    }

    .chart-grid-main {
        display:grid;
        grid-template-columns:2fr 1fr;
        gap:18px;
    }

    .chart-grid-secondary {
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:18px;
    }

    .chart-head {
        padding:22px 22px 0 22px;
    }

    .chart-title {
        font-size:16px;
        font-weight:700;
        margin-bottom:6px;
    }

    .chart-subtitle {
        color:#64748b;
        font-size:14px;
    }

    .chart-body {
        padding:18px 22px 22px 22px;
    }

    .bars-chart-wrap {
        height:320px;
        display:flex;
        flex-direction:column;
        justify-content:flex-end;
        gap:10px;
    }

    .bars-grid {
        flex:1;
        display:flex;
        align-items:flex-end;
        gap:18px;
        border-left:2px solid #cbd5e1;
        border-bottom:2px solid #cbd5e1;
        padding:16px 16px 0 16px;
        position:relative;
        min-height:260px;
        background-image:
            linear-gradient(to top, rgba(148, 163, 184, 0.18) 1px, transparent 1px);
        background-size:100% 25%;
    }

    .bar-group {
        flex:1;
        min-width:0;
        display:flex;
        flex-direction:column;
        justify-content:flex-end;
        align-items:center;
        gap:10px;
        height:100%;
    }

    .bar-stack {
        width:100%;
        display:flex;
        align-items:flex-end;
        justify-content:center;
        gap:8px;
        height:100%;
    }

    .bar {
        width:32px;
        max-width:42%;
        border-radius:10px 10px 0 0;
        position:relative;
    }

    .bar-sales {
        background:#2340b3;
    }

    .bar-costs {
        background:#6366f1;
    }

    .bar-value {
        position:absolute;
        bottom:calc(100% + 6px);
        left:50%;
        transform:translateX(-50%);
        font-size:11px;
        color:#475569;
        white-space:nowrap;
    }

    .bar-label {
        font-size:14px;
        color:#64748b;
    }

    .chart-legend {
        display:flex;
        justify-content:center;
        gap:16px;
        flex-wrap:wrap;
        margin-top:4px;
    }

    .legend-item {
        display:flex;
        align-items:center;
        gap:8px;
        font-size:14px;
        color:#475569;
    }

    .legend-dot {
        width:12px;
        height:12px;
        border-radius:3px;
    }

    .legend-sales {
        background:#2340b3;
    }

    .legend-costs {
        background:#6366f1;
    }

    .donut-wrap {
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        gap:18px;
        min-height:320px;
    }

    .donut-chart {
        width:150px;
        height:150px;
        border-radius:50%;
        position:relative;
        background:conic-gradient(#e2e8f0 0 100%);
    }

    .donut-chart::after {
        content:'';
        position:absolute;
        inset:20px;
        border-radius:50%;
        background:#fff;
    }

    .donut-center {
        position:absolute;
        inset:0;
        display:flex;
        align-items:center;
        justify-content:center;
        flex-direction:column;
        z-index:2;
        text-align:center;
        pointer-events:none;
    }

    .donut-center-value {
        font-size:16px;
        font-weight:700;
    }

    .donut-center-label {
        font-size:12px;
        color:#64748b;
    }

    .category-legend {
        width:100%;
        display:flex;
        flex-direction:column;
        gap:12px;
    }

    .category-row {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        font-size:14px;
    }

    .category-left {
        display:flex;
        align-items:center;
        gap:10px;
        min-width:0;
    }

    .category-name {
        color:#475569;
    }

    .category-percent {
        font-weight:700;
    }

    .line-chart-wrap {
        min-height:320px;
        display:flex;
        flex-direction:column;
        gap:14px;
    }

    .line-chart-svg {
        width:100%;
        height:260px;
        display:block;
        border-left:2px solid #cbd5e1;
        border-bottom:2px solid #cbd5e1;
        background-image:
            linear-gradient(to top, rgba(148, 163, 184, 0.18) 1px, transparent 1px);
        background-size:100% 25%;
    }

    .line-labels {
        display:grid;
        grid-template-columns:repeat(7, 1fr);
        gap:8px;
        padding-left:10px;
    }

    .line-label {
        text-align:center;
        color:#64748b;
        font-size:14px;
    }

    .list-card-body {
        padding:8px 22px 22px 22px;
    }

    .product-row,
    .method-row {
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:16px;
        padding:16px 0;
        border-bottom:1px solid #e5e7eb;
    }

    .product-row:last-child,
    .method-row:last-child {
        border-bottom:none;
    }

    .product-name {
        font-weight:700;
        margin-bottom:6px;
    }

    .product-meta {
        font-size:13px;
        color:#64748b;
    }

    .product-amount {
        font-weight:700;
        color:#2340b3;
        white-space:nowrap;
    }

    .method-name {
        font-weight:700;
        margin-bottom:8px;
    }

    .method-bar-wrap {
        width:100%;
        height:8px;
        border-radius:999px;
        background:#e5e7eb;
        overflow:hidden;
        margin-bottom:8px;
    }

    .method-bar {
        height:100%;
        border-radius:999px;
        background:#2340b3;
    }

    .method-meta {
        font-size:13px;
        color:#64748b;
    }

    .method-right {
        font-weight:700;
        white-space:nowrap;
        padding-top:2px;
    }

    .empty-state {
        color:#64748b;
        text-align:center;
        padding:30px 10px;
    }

    @media print {
        .reports-actions,
        .topbar,
        .sidebar,
        .branch-selector,
        .user-box,
        .btn {
            display:none !important;
        }

        .main-wrapper,
        .page-content {
            padding:0 !important;
        }

        .reports-wrap {
            gap:14px;
        }

        .summary-card,
        .chart-card,
        .list-card {
            break-inside:avoid;
        }
    }

    @media (max-width: 1200px) {
        .summary-grid,
        .chart-grid-main,
        .chart-grid-secondary {
            grid-template-columns:1fr;
        }
    }
</style>

<div class="reports-wrap">
    <div class="reports-header">
        <div>
            <h1 style="font-size:32px; margin-bottom:8px;">Reportes</h1>
            <p class="text-muted">Analiza el rendimiento de tu negocio.</p>
        </div>

        <div class="reports-actions">
            <select id="reportsPeriod" class="btn">
                <option value="3m">Últimos 3 meses</option>
                <option value="6m" selected>Últimos 6 meses</option>
                <option value="12m">Últimos 12 meses</option>
                <option value="30d">Últimos 30 días</option>
                <option value="90d">Últimos 90 días</option>
            </select>
            <button type="button" class="btn" id="exportPdfButton">Exportar PDF</button>
        </div>
    </div>

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

        <div class="list-card">
            <div class="chart-head">
                <div class="chart-title">Métodos de pago preferidos</div>
                <div class="chart-subtitle">Distribución por monto cobrado</div>
            </div>
            <div class="list-card-body" id="paymentMethodsList"></div>
        </div>
    </div>
</div>

<script>
    const reportsState = {
        period: '6m',
        categoryColors: ['#2340b3', '#0f172a', '#64748b', '#94a3b8', '#cbd5e1', '#818cf8']
    };

    function money(value) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            maximumFractionDigits: 0
        }).format(Number(value || 0));
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
        el.textContent = signedPercent(value);
        el.className = `change-badge ${badgeClass(value)}`;
    }

    async function loadSummary() {
        const { response, data } = await apiFetch(`/api/reports/summary?${currentQuery()}`);
        if (!response.ok) return;

        document.getElementById('summaryIncome').textContent = money(data.income?.value ?? 0);
        document.getElementById('summaryProfit').textContent = money(data.profit?.value ?? 0);
        document.getElementById('summaryMargin').textContent = percent(data.margin?.value ?? 0);
        document.getElementById('summaryRoi').textContent = percent(data.roi?.value ?? 0);
        document.getElementById('summaryIncomeNote').textContent = data.period?.label ?? 'Periodo';

        setChangeBadge('summaryIncomeChange', data.income?.change_percent ?? 0);
        setChangeBadge('summaryProfitChange', data.profit?.change_percent ?? 0);
        setChangeBadge('summaryMarginChange', data.margin?.change_percent ?? 0);
        setChangeBadge('summaryRoiChange', data.roi?.change_percent ?? 0);
    }

    async function loadSalesVsCosts() {
        const { response, data } = await apiFetch(`/api/reports/sales-vs-costs?${currentQuery()}`);
        const container = document.getElementById('salesVsCostsChart');

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
        const { response, data } = await apiFetch(`/api/reports/categories?${currentQuery()}`);
        const donut = document.getElementById('categoryDonut');
        const legend = document.getElementById('categoryLegend');

        if (!response.ok || !Array.isArray(data.items) || data.items.length === 0) {
            donut.style.background = 'conic-gradient(#e2e8f0 0 100%)';
            document.getElementById('categoryDonutTotal').textContent = money(0);
            legend.innerHTML = `<div class="empty-state">No hay categorías para mostrar.</div>`;
            return;
        }

        document.getElementById('categoryDonutTotal').textContent = money(data.total_amount ?? 0);

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
        const { response, data } = await apiFetch(`/api/reports/payment-methods?${currentQuery()}`);
        const container = document.getElementById('paymentMethodsList');

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
        await Promise.all([
            loadSummary(),
            loadSalesVsCosts(),
            loadCategories(),
            loadPeakHours(),
            loadTopProducts(),
            loadPaymentMethods()
        ]);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const periodSelect = document.getElementById('reportsPeriod');
        const exportButton = document.getElementById('exportPdfButton');

        periodSelect.addEventListener('change', () => {
            reportsState.period = periodSelect.value;
            loadReports();
        });

        exportButton.addEventListener('click', () => {
            window.print();
        });

        loadReports();
    });
</script>
@endsection