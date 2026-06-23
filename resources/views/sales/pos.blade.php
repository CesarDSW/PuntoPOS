@php
    $printSettings = \App\Support\CompanyPreference::settings(auth()->user()->company_idfk);
    $salesPosAccess = [
        'create' => \App\Support\UserAccess::has(auth()->user(), 'sales.create'),
        'pos_use' => \App\Support\UserAccess::has(auth()->user(), 'sales.pos.use'),
        'ticket_print' => \App\Support\UserAccess::has(auth()->user(), 'sales.ticket.print'),
        'cash_open' => \App\Support\UserAccess::has(auth()->user(), 'cash.open'),
        'cash_close' => \App\Support\UserAccess::has(auth()->user(), 'cash.close'),
    ];
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS</title>
    
    <link rel="stylesheet" href="{{ asset('css/theme-colors.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/sales/pos.css') }}">
    @stack('styles')
</head>

@php
    $companyIdForTheme = auth()->user()->company_idfk ?? null;
    $prefsForTheme = \App\Support\CompanyPreference::all($companyIdForTheme);

    $themePreference =
        $prefsForTheme['theme'] ??
        $prefsForTheme['theme_mode'] ??
        $prefsForTheme['appearance'] ??
        $prefsForTheme['interface_theme'] ??
        session('theme', 'light');

    if (! in_array($themePreference, ['light', 'dark', 'auto'])) {
        $themePreference = 'light';
    }

    $resolvedTheme = $themePreference === 'auto' ? 'light' : $themePreference;
@endphp

<body
    data-theme="{{ $resolvedTheme }}"
    data-theme-preference="{{ $themePreference }}"
>
    <script>
        (function () {
            const body = document.body;
            const root = document.documentElement;

            const serverTheme =
                body.dataset.themePreference ||
                body.dataset.theme ||
                'light';

            const normalizedPreference = ['light', 'dark', 'auto'].includes(serverTheme)
                ? serverTheme
                : 'light';

            const media = window.matchMedia('(prefers-color-scheme: dark)');

            function resolveTheme(value) {
                if (value === 'auto') {
                    return media.matches ? 'dark' : 'light';
                }

                return value === 'dark' ? 'dark' : 'light';
            }

            function applyTheme() {
                const resolved = resolveTheme(normalizedPreference);

                root.setAttribute('data-theme', resolved);
                body.setAttribute('data-theme', resolved);
                body.setAttribute('data-theme-preference', normalizedPreference);

                localStorage.setItem('punto_theme', normalizedPreference);
                localStorage.setItem('theme', normalizedPreference);
            }

            applyTheme();

            if (normalizedPreference === 'auto') {
                if (typeof media.addEventListener === 'function') {
                    media.addEventListener('change', applyTheme);
                } else if (typeof media.addListener === 'function') {
                    media.addListener(applyTheme);
                }
            }
        })();
    </script>

    <div class="pos-shell">
        <div class="pos-top">
            <div class="top-info">
                <span><span class="dot"></span> <strong>Turno activo</strong></span>
                <span id="posUserName">{{ auth()->user()->name_user }}</span>
                <span id="posBranchName">Sucursal actual</span>
            </div>

            <div class="dropdown-wrap">
                <button class="btn" id="optionsButton">Opciones</button>
                <div class="dropdown-menu" id="optionsMenu">
                    <button class="dropdown-item" id="openCloseShiftButton" type="button" onclick="openCloseShiftModal()">Cerrar turno</button>
                
                    @if($salesPosAccess['cash_close'])
                        <button class="dropdown-item" id="closeCashButton" type="button" onclick="closeCash()">Cerrar caja</button>
                    @endif    
                </div>
            </div>
        </div>

        <div class="pos-main">
            <div class="pos-left">
                <div class="customer-box">
                    <h2 style="font-size:18px;">Cliente</h2>
                    <input
                        type="text"
                        id="customerSearch"
                        class="search-box"
                        placeholder="Buscar cliente por nombre, teléfono o correo..."
                        autocomplete="off"
                        style="margin-top:12px;"
                    >
                    <div class="customer-results" id="customerResults"></div>
                    <div class="selected-customer" id="selectedCustomerCard"></div>
                </div>

                <h2 style="font-size:18px;">Buscar productos</h2>
                <input type="text" id="posSearch" class="search-box" placeholder="Escribe el nombre del producto...">
                <div class="products-grid" id="productsGrid"></div>
            </div>

            <div class="pos-right">
                <div class="cart-head">
                    <strong>Venta actual</strong>
                    <button type="button" class="btn" style="padding:8px 12px;" onclick="clearCart()">Limpiar</button>
                </div>

                <div class="cart-items" id="cartItems">
                    <div class="cart-empty">No hay productos<br>Busca y agrega productos</div>
                </div>

                <div class="cart-footer">
                    <div class="total-row"><span>Subtotal</span><strong id="cartSubtotal">$0</strong></div>
                    <div class="total-row"><span>IVA (16%)</span><strong id="cartIva">$0</strong></div>
                    <div class="total-main"><span>Total</span><span id="cartTotal">$0</span></div>
                    <button class="btn btn-primary btn-block" id="payButton" type="button" onclick="openPaymentModal()" disabled>Cobrar $0</button>
                </div>
            </div>
        </div>
    </div>

    <div class="overlay" id="cashModalOverlay">
        <div class="modal">
            <div class="modal-head">
                <div>
                    <div style="font-size:28px; font-weight:700;">Apertura de caja</div>
                    <div style="color:#64748b; margin-top:6px;">Ingresa el monto inicial para comenzar operaciones</div>
                </div>
            </div>
            <div class="modal-body">
                <div class="field">
                    <label class="label">Monto inicial en efectivo</label>
                    <input type="number" step="0.01" min="0" id="openingAmount" class="input" placeholder="0.00">
                </div>
                <div class="field">
                    <label class="label">Observaciones</label>
                    <textarea id="openingNotes" class="textarea" placeholder="Ej: billetes revisados, fondo completo..."></textarea>
                </div>
                <div class="error-box" id="cashErrorBox"></div>
            </div>
            <div class="modal-foot">
                <a href="/ventas" class="btn" style="text-decoration:none;">Cancelar</a>
                <button class="btn btn-primary" id="openCashButton" type="button" onclick="openCash()">Abrir caja</button>
            </div>
        </div>
    </div>

    <div class="overlay" id="shiftModalOverlay">
        <div class="modal shift-open-modal">
            <div class="shift-open-body">
                <div class="shift-open-top">
                    <div class="shift-open-icon">🕒</div>
                    <div class="shift-open-title">Apertura de turno</div>
                    <div class="shift-open-subtitle">Confirma el inicio de tu turno de trabajo</div>
                </div>

                <div class="shift-open-alert">
                    <div class="shift-open-alert-icon">i</div>
                    <div>
                        <div style="font-weight:700; margin-bottom:6px;">Caja abierta</div>
                        <div style="color:#64748b; font-size:14px; line-height:1.45;">
                            La caja del punto de venta ya está abierta. Solo necesitas iniciar tu turno.
                        </div>
                    </div>
                </div>

                <div class="shift-open-user-card">
                    <div class="shift-open-avatar" id="shiftOpenAvatar">
                        {{ strtoupper(substr(auth()->user()->name_user, 0, 1)) }}
                    </div>
                    <div>
                        <div class="shift-open-user-name" id="shiftOpenUserName">{{ auth()->user()->name_user }}</div>
                        <div class="shift-open-user-role">Administrador</div>
                    </div>
                </div>

                <div class="shift-open-info-card">
                    <div class="shift-open-info-icon">🕒</div>
                    <div>
                        <div class="shift-open-info-label">Fecha y hora de inicio</div>
                        <div class="shift-open-info-value" id="shiftOpenDateTime">-</div>
                    </div>
                </div>

                <div class="shift-open-info-card">
                    <div class="shift-open-info-icon">👤</div>
                    <div>
                        <div class="shift-open-info-label">Tipo de turno</div>
                        <div class="shift-open-info-value">Turno de cajero</div>
                    </div>
                </div>

                <div class="shift-open-checks">
                    <div class="shift-open-checks-title">Verificaciones</div>
                    <div class="shift-open-check-list">
                        <div class="shift-open-check-item">
                            <span class="shift-open-check-badge">✓</span>
                            <span>Caja abierta y lista</span>
                        </div>
                        <div class="shift-open-check-item">
                            <span class="shift-open-check-badge">✓</span>
                            <span>Área de trabajo preparada</span>
                        </div>
                        <div class="shift-open-check-item">
                            <span class="shift-open-check-badge">✓</span>
                            <span>Conexión a internet estable</span>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="shiftType" class="shift-open-hidden-input" value="CAJERO">
                <div class="error-box" id="shiftErrorBox"></div>
            </div>

            <div class="shift-open-foot">
                <a href="/ventas" class="btn" style="text-decoration:none; text-align:center;">Cancelar</a>
                <button class="btn btn-primary" id="openShiftButton" type="button" onclick="openShift()">Iniciar turno</button>
            </div>
        </div>
    </div>

    <div class="overlay" id="paymentModalOverlay">
        <div class="modal">
            <div class="modal-head">
                <div>
                    <div style="font-size:18px; font-weight:700;">Método de pago</div>
                </div>
                <button class="btn" type="button" onclick="closePaymentModal()">Cerrar</button>
            </div>
            <div class="modal-body">
                <div class="pay-method active" data-method="EFECTIVO" onclick="selectMethod('EFECTIVO')" id="methodEFECTIVO">Efectivo</div>
                <div class="pay-method" data-method="TARJETA" onclick="selectMethod('TARJETA')" id="methodTARJETA">Tarjeta</div>
                <div class="pay-method" data-method="TRANSFERENCIA" onclick="selectMethod('TRANSFERENCIA')" id="methodTRANSFERENCIA">Transferencia</div>

                <div class="info-card">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span>Cliente</span>
                        <strong id="paymentCustomerText">Cliente general</strong>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span>Total a cobrar</span>
                        <strong id="paymentTotalText">$0</strong>
                    </div>
                </div>

                <div id="cashFields">
                    <div class="field">
                        <label class="label">Monto recibido</label>
                        <input type="number" step="0.01" min="0" id="amountPaid" class="input" placeholder="0.00" oninput="renderPaymentTotals()">
                    </div>

                    <div class="info-card">
                        <div style="display:flex; justify-content:space-between;">
                            <span>Cambio</span>
                            <strong id="changeGivenText">$0</strong>
                        </div>
                    </div>
                </div>

                <div id="referenceWrap" style="display:none;">
                    <div class="field">
                        <label class="label">Banco</label>
                        <select id="paymentBank" class="input">
                            <option value="">Selecciona un banco</option>
                            <option value="BBVA">BBVA</option>
                            <option value="Santander">Santander</option>
                            <option value="Banorte">Banorte</option>
                            <option value="Banamex">Banamex</option>
                            <option value="HSBC">HSBC</option>
                            <option value="Scotiabank">Scotiabank</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="field" id="cardLast4Wrap" style="display:none;">
                        <label class="label">Últimos 4 dígitos de la tarjeta</label>
                        <input
                            type="text"
                            id="cardLast4"
                            class="input"
                            maxlength="4"
                            inputmode="numeric"
                            placeholder="1234"
                        >
                    </div>

                    <div class="field">
                        <label class="label" id="referencePaymentLabel">Código de autorización</label>
                        <input
                            type="text"
                            id="referencePayment"
                            class="input"
                            placeholder="Ej. APROBADA-001"
                        >
                    </div>

                    <div class="info-card" id="electronicSimulationHelp" style="font-size:14px; line-height:1.45;">
                        <strong>Simulación local:</strong>
                        Probar una simulación para rechazar tarjeta utiliza las siguientes palabras
                        <strong>RECHAZADA</strong>, <strong>DECLINED</strong>, <strong>FAIL</strong>,
                        <strong>ERROR</strong> o <strong>DENIED</strong>.
                        En tarjeta, también se rechaza con últimos 4 dígitos
                        <strong>0000</strong>, <strong>1111</strong> o <strong>9999</strong>.
                    </div>
                </div>

                <div class="error-box" id="paymentErrorBox"></div>
            </div>
            <div class="modal-foot">
                <button class="btn" type="button" onclick="closePaymentModal()">Guardar pendiente y cerrar</button>
                <button class="btn" type="button" id="cancelSaleButton" onclick="cancelCurrentSale()">Cancelar venta</button>
                <button class="btn btn-primary" type="button" id="confirmSaleButton" onclick="confirmSale()">Confirmar</button>
            </div>
        </div>
    </div>

    <div class="overlay" id="successModalOverlay">
        <div class="modal">
            <div class="modal-head">
                <div>
                    <div style="font-size:32px; font-weight:700;">¡Venta confirmada!</div>
                    <div style="color:#64748b; margin-top:6px;">La transacción se ha procesado exitosamente</div>
                </div>
            </div>
            <div class="modal-body">
                <div class="info-card" id="successInfo"></div>
                <div class="info-card" id="successItems"></div>
            </div>
            <div class="modal-foot" style="flex-direction:column;">
                @if($salesPosAccess['ticket_print'])
                    <button class="btn btn-primary btn-block" type="button" id="viewTicketBtn">Ver e imprimir ticket</button>
                @endif

                <button class="btn btn-block" type="button" onclick="startNewSale()">Nueva venta</button>
            </div>
        </div>
    </div>

    <div class="overlay" id="closeShiftOverlay">
        <div class="modal modal-large">
            <div class="modal-head">
                <div>
                    <div style="font-size:28px; font-weight:700;">Cierre de turno</div>
                    <div style="color:#64748b; margin-top:6px;">Resumen de actividad del turno</div>
                </div>
                <button class="btn" type="button" onclick="closeCloseShiftModal()">Cerrar</button>
            </div>

            <div class="modal-body">
                <div class="shift-top-grid">
                    <div>
                        <div style="color:#64748b; margin-bottom:8px;">Usuario</div>
                        <div style="font-size:18px; font-weight:700;" id="closeShiftUserName">-</div>

                        <div style="color:#64748b; margin-top:18px; margin-bottom:8px;">Inicio</div>
                        <div style="font-size:18px; font-weight:700;" id="closeShiftStartTime">-</div>
                    </div>

                    <div>
                        <div style="color:#64748b; margin-bottom:8px;">Duración</div>
                        <div style="font-size:18px; font-weight:700;" id="closeShiftDuration">-</div>

                        <div style="color:#64748b; margin-top:18px; margin-bottom:8px;">Fin</div>
                        <div style="font-size:18px; font-weight:700;" id="closeShiftEndTime">-</div>
                    </div>
                </div>

                <div class="shift-summary-grid">
                    <div class="shift-summary-card">
                        <div style="color:#64748b; margin-bottom:10px;">Total vendido</div>
                        <div style="font-size:22px; font-weight:700;" id="closeShiftTotalSold">$0</div>
                    </div>

                    <div class="shift-summary-card">
                        <div style="color:#64748b; margin-bottom:10px;">Ventas realizadas</div>
                        <div style="font-size:22px; font-weight:700;" id="closeShiftSalesCount">0</div>
                    </div>

                    <div class="shift-summary-card">
                        <div style="color:#64748b; margin-bottom:10px;">Ticket promedio</div>
                        <div style="font-size:22px; font-weight:700;" id="closeShiftAvgTicket">$0</div>
                    </div>
                </div>

                <div class="method-box">
                    <div style="font-size:22px; font-weight:700; margin-bottom:14px;">Ventas por método de pago</div>

                    <div class="method-row">
                        <div><span class="method-dot dot-card"></span>Tarjeta</div>
                        <strong id="closeShiftCardCount">0 ventas</strong>
                    </div>

                    <div class="method-row">
                        <div><span class="method-dot dot-cash"></span>Efectivo</div>
                        <strong id="closeShiftCashCount">0 ventas</strong>
                    </div>

                    <div class="method-row">
                        <div><span class="method-dot dot-transfer"></span>Transferencia</div>
                        <strong id="closeShiftTransferCount">0 ventas</strong>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Observaciones del turno (opcional)</label>
                    <textarea id="closeShiftNotes" class="textarea" placeholder="Agrega comentarios sobre el turno..."></textarea>
                </div>

                <div class="info-card">
                    <strong>Importante:</strong> Al cerrar tu turno, la caja permanecerá abierta para que puedas cerrarla más tarde desde el sistema o desde el POS.
                </div>

                <div class="error-box" id="closeShiftErrorBox"></div>
            </div>

            <div class="modal-foot">
                <button class="btn" type="button" onclick="closeCloseShiftModal()">Cancelar</button>
                <button class="btn btn-primary" id="confirmCloseShiftButton" type="button" onclick="confirmCloseShift()">Confirmar cierre de turno</button>
            </div>
        </div>
    </div>

    <div class="overlay" id="shiftClosedSuccessOverlay">
        <div class="modal shift-closed-modal">
            <div class="shift-closed-body">
                <div class="shift-closed-top">
                    <div class="shift-closed-icon">✓</div>
                    <div class="shift-closed-title">Turno cerrado<br>correctamente</div>
                    <div class="shift-closed-subtitle">
                        Tu turno ha finalizado. La caja permanecerá abierta.
                    </div>
                </div>

                <div class="shift-closed-summary">
                    <div class="shift-closed-summary-title">
                        <span class="shift-closed-summary-icon">🕒</span>
                        <span>Resumen del turno</span>
                    </div>

                    <div class="shift-closed-divider"></div>

                    <div class="shift-closed-row">
                        <span class="shift-closed-label">Usuario</span>
                        <span class="shift-closed-value" id="shiftClosedUser">-</span>
                    </div>

                    <div class="shift-closed-row">
                        <span class="shift-closed-label">Duración</span>
                        <span class="shift-closed-value" id="shiftClosedDuration">-</span>
                    </div>

                    <div class="shift-closed-row">
                        <span class="shift-closed-label">Ventas realizadas</span>
                        <span class="shift-closed-value" id="shiftClosedSalesCount">0</span>
                    </div>

                    <div class="shift-closed-total">
                        <span>Total vendido</span>
                        <span class="shift-closed-total-amount" id="shiftClosedTotalSold">$0</span>
                    </div>
                </div>

                <div class="shift-closed-note">
                    <strong>La caja permanece abierta:</strong> Puedes cerrarla después desde la pantalla de ventas o volver a iniciar turno.
                </div>

                <div class="shift-closed-actions">
                    <button class="btn btn-primary btn-block" type="button" onclick="backToSystem()">
                        ← Volver al sistema
                    </button>
                    <button class="btn btn-block" type="button" onclick="restartShiftFlow()">
                        Iniciar nuevo turno
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="overlay" id="closeCashOverlay">
        <div class="modal">
            <div class="modal-head">
                <div>
                    <div style="font-size:28px; font-weight:700;">Cierre de caja</div>
                    <div style="color:#64748b; margin-top:6px;">
                        Ingresa el monto final registrado al terminar operaciones
                    </div>
                </div>
                <button class="btn" type="button" onclick="closeCashModal()">Cerrar</button>
            </div>

            <div class="modal-body">
                <div class="info-card">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span>Usuario</span>
                        <strong id="closeCashUserName">-</strong>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span>Sucursal</span>
                        <strong id="closeCashBranchName">-</strong>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Monto final de caja</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        id="closeCashAmount"
                        class="input"
                        placeholder="0.00"
                    >
                </div>

                <div class="field">
                    <label class="label">Observaciones (opcional)</label>
                    <textarea
                        id="closeCashNotes"
                        class="textarea"
                        placeholder="Ej: sobrante, faltante, corte revisado..."
                    ></textarea>
                </div>

                <div class="error-box" id="closeCashErrorBox"></div>
            </div>

            <div class="modal-foot">
                <button class="btn" type="button" onclick="closeCashModal()">Cancelar</button>
                <button class="btn btn-primary" id="confirmCloseCashButton" type="button" onclick="confirmCloseCash()">
                    Confirmar cierre
                </button>
            </div>
        </div>
    </div>

    <div class="overlay" id="appDialogOverlay">
        <div class="modal">
            <div class="modal-head">
                <div>
                    <div id="appDialogTitle" style="font-size:22px; font-weight:700;">Mensaje</div>
                    <div id="appDialogSubtitle" style="color:#64748b; margin-top:6px;">Aviso del sistema</div>
                </div>
                <button type="button" class="btn" onclick="closeAppDialog()">Cerrar</button>
            </div>

            <div class="modal-body">
                <div class="info-card">
                    <div id="appDialogMessage"></div>
                </div>
            </div>

            <div class="modal-foot" id="appDialogActions">
                <button type="button" class="btn" onclick="closeAppDialog()">Cerrar</button>
            </div>
        </div>
    </div>


    <script>
        window.appPreferences = @json(\App\Support\CompanyPreference::all(auth()->user()->company_idfk ?? null));

        window.appFormat = window.appFormat || {
            money(value) {
                const prefs = window.appPreferences || {};
                const currency = prefs.currency || 'MXN';
                const decimals = Number(prefs.price_decimals ?? 2);

                return new Intl.NumberFormat('es-MX', {
                    style: 'currency',
                    currency: currency,
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                }).format(Number(value || 0));
            },

            normalizeDate(value) {
                if (!value) return null;

                if (value instanceof Date) {
                    return value;
                }

                const stringValue = String(value).replace(' ', 'T');
                const date = new Date(stringValue);

                return isNaN(date.getTime()) ? null : date;
            },

            date(value) {
                const date = this.normalizeDate(value);

                if (!date) {
                    return value || '-';
                }

                return new Intl.DateTimeFormat('es-MX', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    timeZone: window.appPreferences?.timezone || 'America/Mexico_City'
                }).format(date);
            },

            time(value) {
                const date = this.normalizeDate(value);

                if (!date) {
                    return value || '-';
                }

                return new Intl.DateTimeFormat('es-MX', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                    timeZone: window.appPreferences?.timezone || 'America/Mexico_City'
                }).format(date);
            },

            dateTime(value) {
                const date = this.normalizeDate(value);

                if (!date) {
                    return value || '-';
                }

                return new Intl.DateTimeFormat('es-MX', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                    timeZone: window.appPreferences?.timezone || 'America/Mexico_City'
                }).format(date);
            }
        };

        const salesPosAccess = @json($salesPosAccess);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const posPrintConfig = {
            autoPrint: @json((bool) ($printSettings?->auto_print ?? false)),
            printerWidth: @json($printSettings?->printer_width ?? '80mm'),
            showTaxes: @json((bool) ($printSettings?->show_taxes ?? true))
        };

        let cart = [];
        let productsCache = [];
        let customersCache = [];
        let selectedCustomer = null;
        let paymentMethod = 'EFECTIVO';
        let lastSaleId = null;
        let currentShiftSummary = null;

        let currentPendingSaleId = null;
        let isSavingPendingSale = false;

        let productsController = null;
        let customersController = null;
        let productsRequestId = 0;
        let customersRequestId = 0;

        const productSearchMemory = new Map();
        const customerSearchMemory = new Map();

        let isOpeningCash = false;
        let isOpeningShift = false;
        let isClosingShift = false;
        let isClosingCash = false;
        let isConfirmingSale = false;
        let isCancellingSale = false;

        let appDialogResolver = null;

        function money(value) {
            return window.appFormat.money(value);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function debounce(fn, delay = 350) {
            let timer = null;

            return function (...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        function showError(id, message) {
            const box = document.getElementById(id);
            if (!box) return;
            box.textContent = message;
            box.style.display = 'block';
        }

        function hideError(id) {
            const box = document.getElementById(id);
            if (!box) return;
            box.textContent = '';
            box.style.display = 'none';
        }

        function closeAppDialog() {
            document.getElementById('appDialogOverlay').classList.remove('show');
            document.getElementById('appDialogTitle').textContent = 'Mensaje';
            document.getElementById('appDialogSubtitle').textContent = 'Aviso del sistema';
            document.getElementById('appDialogMessage').innerHTML = '';
            document.getElementById('appDialogActions').innerHTML = `
                <button type="button" class="btn" onclick="closeAppDialog()">Cerrar</button>
            `;

            if (appDialogResolver) {
                appDialogResolver(false);
                appDialogResolver = null;
            }
        }

        function showAppAlert(message, title = 'Aviso', subtitle = 'Mensaje del sistema') {
            document.getElementById('appDialogTitle').textContent = title;
            document.getElementById('appDialogSubtitle').textContent = subtitle;
            document.getElementById('appDialogMessage').innerHTML = message;
            document.getElementById('appDialogActions').innerHTML = `
                <button type="button" class="btn btn-primary" onclick="closeAppDialog()">Aceptar</button>
            `;
            document.getElementById('appDialogOverlay').classList.add('show');
        }

        function showAppConfirm(message, title = 'Confirmar acción', subtitle = 'Esta acción requiere confirmación') {
            return new Promise((resolve) => {
                appDialogResolver = resolve;

                document.getElementById('appDialogTitle').textContent = title;
                document.getElementById('appDialogSubtitle').textContent = subtitle;
                document.getElementById('appDialogMessage').innerHTML = message;
                document.getElementById('appDialogActions').innerHTML = `
                    <button type="button" class="btn" onclick="resolveAppDialog(false)">No</button>
                    <button type="button" class="btn btn-primary" onclick="resolveAppDialog(true)">Sí, continuar</button>
                `;
                document.getElementById('appDialogOverlay').classList.add('show');
            });
        }

        function resolveAppDialog(result) {
            document.getElementById('appDialogOverlay').classList.remove('show');
            document.getElementById('appDialogTitle').textContent = 'Mensaje';
            document.getElementById('appDialogSubtitle').textContent = 'Aviso del sistema';
            document.getElementById('appDialogMessage').innerHTML = '';
            document.getElementById('appDialogActions').innerHTML = `
                <button type="button" class="btn" onclick="closeAppDialog()">Cerrar</button>
            `;

            if (appDialogResolver) {
                appDialogResolver(result);
                appDialogResolver = null;
            }
        }

        function setButtonLoading(buttonId, isLoading, loadingText) {
            const button = document.getElementById(buttonId);
            if (!button) return;

            if (!button.dataset.originalText) {
                button.dataset.originalText = button.textContent.trim();
            }

            button.disabled = isLoading;
            button.textContent = isLoading
                ? loadingText
                : button.dataset.originalText;
        }

        function getUserInitials(name) {
            const parts = String(name || '')
                .trim()
                .split(/\s+/)
                .filter(Boolean);

            if (parts.length === 0) return 'U';
            if (parts.length === 1) return parts[0].substring(0, 1).toUpperCase();

            return (parts[0][0] + parts[1][0]).toUpperCase();
        }

        function formatShiftOpenDateTime(date = new Date()) {
            return window.appFormat.dateTime(date);
        }

        function prepareShiftOpenModal() {
            const userName = document.getElementById('posUserName').textContent.trim() || '{{ auth()->user()->name_user }}';
            document.getElementById('shiftOpenUserName').textContent = userName;
            document.getElementById('shiftOpenAvatar').textContent = getUserInitials(userName);
            document.getElementById('shiftOpenDateTime').textContent = formatShiftOpenDateTime(new Date());
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

                return { response, data, aborted: false };
            } catch (error) {
                if (error.name === 'AbortError') {
                    return {
                        response: { ok: false, status: 0 },
                        data: {},
                        aborted: true
                    };
                }

                return {
                    response: { ok: false, status: 0 },
                    data: { message: error.message || 'Error de red.' },
                    aborted: false
                };
            }
        }

        function cartSubtotal() {
            return cart.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
        }

        function cartIva() {
            return cartSubtotal() * 0.16;
        }

        function cartTotal() {
            return cartSubtotal() + cartIva();
        }

        function resetPaymentDraftFields() {
            const amountPaid = document.getElementById('amountPaid');
            const referencePayment = document.getElementById('referencePayment');
            const changeGivenText = document.getElementById('changeGivenText');
            const paymentBank = document.getElementById('paymentBank');
            const cardLast4 = document.getElementById('cardLast4');

            if (amountPaid) amountPaid.value = '';
            if (referencePayment) referencePayment.value = '';
            if (changeGivenText) changeGivenText.textContent = money(0);
            if (paymentBank) paymentBank.value = '';
            if (cardLast4) cardLast4.value = '';
        }

        function delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        function getElectronicReferenceParts() {
            const bank = document.getElementById('paymentBank')?.value.trim() || '';
            const cardLast4 = document.getElementById('cardLast4')?.value.trim() || '';
            const reference = document.getElementById('referencePayment')?.value.trim() || '';

            return { bank, cardLast4, reference };
        }

        function buildElectronicReference() {
        if (paymentMethod === 'TARJETA') {
            return 'Pago con tarjeta';
        }

        if (paymentMethod === 'TRANSFERENCIA') {
            return 'Pago por transferencia';
        }

        return 'Pago electrónico';
    }

        function validateElectronicFields() {
            const { bank, cardLast4, reference } = getElectronicReferenceParts();

            if (!bank) {
                return 'Selecciona el banco.';
            }

            if (paymentMethod === 'TARJETA') {
                if (!/^\d{4}$/.test(cardLast4)) {
                    return 'Ingresa los últimos 4 dígitos de la tarjeta.';
                }

                if (reference.length < 4) {
                    return 'Ingresa un código de autorización válido.';
                }
            }

            if (paymentMethod === 'TRANSFERENCIA') {
                if (reference.length < 6) {
                    return 'Ingresa una referencia de transferencia válida.';
                }
            }

            return null;
        }

        async function simulateElectronicAuthorization() {
            const { bank, cardLast4, reference } = getElectronicReferenceParts();
            const normalizedReference = reference.toUpperCase();

            await delay(1400);

            const forcedReject =
                normalizedReference.includes('RECHAZADA') ||
                normalizedReference.includes('RECHAZADO') ||
                normalizedReference.includes('DECLINED') ||
                normalizedReference.includes('FAIL') ||
                normalizedReference.includes('ERROR') ||
                normalizedReference.includes('DENIED');

            if (paymentMethod === 'TARJETA') {
                if (['0000', '1111', '9999'].includes(cardLast4)) {
                    return {
                        approved: false,
                        reason: `El banco ${bank} rechazó la autorización de la tarjeta terminación ${cardLast4}.`
                    };
                }

                if (forcedReject) {
                    return {
                        approved: false,
                        reason: `El banco ${bank} rechazó la autorización de la tarjeta.`
                    };
                }

                return {
                    approved: true,
                    reason: `Autorización aprobada por ${bank}.`
                };
            }

            if (forcedReject) {
                return {
                    approved: false,
                    reason: `La referencia de transferencia fue rechazada por ${bank}.`
                };
            }

            return {
                approved: true,
                reason: `Transferencia validada correctamente por ${bank}.`
            };
        }

        async function cancelSaleAfterRejectedPayment(reasonMessage) {
            const { response, data } = await apiFetch(`/api/sales/${currentPendingSaleId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            if (!response.ok) {
                showError(
                    'paymentErrorBox',
                    data.errors?.sale?.[0] ||
                    data.message ||
                    'El pago fue rechazado, pero no se pudo cancelar la venta.'
                );
                return false;
            }

            document.getElementById('paymentModalOverlay').classList.remove('show');
            resetActiveSaleState();

            showAppAlert(
                `${reasonMessage}<br><br><strong>La venta fue cancelada automáticamente.</strong>`,
                'Pago rechazado',
                'La operación no fue autorizada'
            );

            return true;
        }

        function buildSalePayload() {
            const payload = {
                payment_method: paymentMethod,
                items: cart.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity
                }))
            };

            if (selectedCustomer?.customer_id) {
                payload.customer_id = selectedCustomer.customer_id;
            }

            if (paymentMethod === 'EFECTIVO') {
                payload.amount_paid = document.getElementById('amountPaid')?.value || 0;
            } else {
                payload.reference_payment = buildElectronicReference();
            }

            return payload;
        }

        async function savePendingSale(showInlineError = true) {
            if (!cart.length) {
                return { ok: false, skipped: true };
            }

            if (isSavingPendingSale) {
                return { ok: true, busy: true };
            }

            isSavingPendingSale = true;

            try {
                const payload = buildSalePayload();
                payload.sale_id = currentPendingSaleId;

                const { response, data } = await apiFetch('/api/sales/pending', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    if (showInlineError) {
                        showError(
                            'paymentErrorBox',
                            data.errors?.items?.[0] ||
                            data.errors?.customer_id?.[0] ||
                            data.errors?.amount_paid?.[0] ||
                            data.message ||
                            'No se pudo guardar la venta pendiente.'
                        );
                    }

                    return { ok: false, data };
                }

                const sale = data.data || data;
                currentPendingSaleId = sale.sale_id ?? currentPendingSaleId;

                return { ok: true, data: sale };
            } finally {
                isSavingPendingSale = false;
            }
        }

        function resetActiveSaleState() {
            currentPendingSaleId = null;
            paymentMethod = 'EFECTIVO';

            cart = [];
            renderCart();

            productsCache = [];
            renderProducts([], '');
            document.getElementById('posSearch').value = '';

            clearSelectedCustomer();
            resetPaymentDraftFields();
            hideError('paymentErrorBox');
            applyPaymentMethodUI();
        }

        function renderSelectedCustomer() {
            const card = document.getElementById('selectedCustomerCard');

            if (!selectedCustomer) {
                card.innerHTML = `
                    <div class="selected-customer-label">Cliente seleccionado</div>
                    <div class="selected-customer-top">
                        <div>
                            <div class="selected-customer-name">Cliente general</div>
                            <div class="selected-customer-meta">Se usará automáticamente si no eliges otro cliente.</div>
                        </div>
                    </div>
                `;
                return;
            }

            card.innerHTML = `
                <div class="selected-customer-label">Cliente seleccionado</div>
                <div class="selected-customer-top">
                    <div>
                        <div class="selected-customer-name">${escapeHtml(selectedCustomer.name_customer)}</div>
                        <div class="selected-customer-meta">
                            ${escapeHtml(selectedCustomer.phone || 'Sin teléfono')} · ${escapeHtml(selectedCustomer.email || 'Sin correo')}
                        </div>
                    </div>
                    <button type="button" class="selected-customer-clear" onclick="clearSelectedCustomer()">Quitar</button>
                </div>
            `;
        }

        function hideCustomerResults() {
            document.getElementById('customerResults').classList.remove('show');
        }

        function renderCustomerResultsLoading() {
            const results = document.getElementById('customerResults');
            results.innerHTML = `<div class="customer-empty">Buscando clientes...</div>`;
            results.classList.add('show');
        }


        function renderCustomerResults(items) {
            const results = document.getElementById('customerResults');

            if (!items.length) {
                results.innerHTML = `<div class="customer-empty">No se encontraron clientes.</div>`;
                results.classList.add('show');
                return;
            }

            results.innerHTML = items.map(item => `
                <button type="button" class="customer-option" onclick="selectCustomer(${Number(item.customer_id)})">
                    <div class="customer-option-name">${escapeHtml(item.name_customer)}</div>
                    <div class="customer-option-meta">${escapeHtml(item.phone || 'Sin teléfono')} · ${escapeHtml(item.email || 'Sin correo')}</div>
                </button>
            `).join('');

            results.classList.add('show');
        }

        async function loadCustomers() {
            const search = document.getElementById('customerSearch').value.trim();
            const normalizedSearch = search.toLowerCase();
            const currentRequestId = ++customersRequestId;

            if (customerSearchMemory.has(normalizedSearch)) {
                customersCache = customerSearchMemory.get(normalizedSearch);
                renderCustomerResults(customersCache);
                return;
            }

            if (customersController) {
                customersController.abort();
            }

            customersController = new AbortController();
            renderCustomerResultsLoading();

            const { response, data, aborted } = await apiFetch(
                `/api/sales/pos/customers?search=${encodeURIComponent(search)}&limit=10`,
                { signal: customersController.signal }
            );

            if (aborted || currentRequestId !== customersRequestId) return;

            if (!response.ok) {
                customersCache = [];
                hideCustomerResults();
                return;
            }

            customersCache = Array.isArray(data) ? data : [];
            customerSearchMemory.set(normalizedSearch, customersCache);
            renderCustomerResults(customersCache);
        }

        function selectCustomer(customerId) {
            const customer = customersCache.find(item => Number(item.customer_id) === Number(customerId));
            if (!customer) return;

            selectedCustomer = customer;
            document.getElementById('customerSearch').value = customer.name_customer;
            renderSelectedCustomer();
            hideCustomerResults();
        }

        function clearSelectedCustomer() {
            selectedCustomer = null;
            document.getElementById('customerSearch').value = '';
            renderSelectedCustomer();
            hideCustomerResults();
        }

        function renderCart() {
            const container = document.getElementById('cartItems');
            const subtotal = cartSubtotal();
            const iva = cartIva();
            const total = cartTotal();

            document.getElementById('cartSubtotal').textContent = money(subtotal);
            document.getElementById('cartIva').textContent = money(iva);
            document.getElementById('cartTotal').textContent = money(total);

            const payButton = document.getElementById('payButton');
            payButton.disabled = cart.length === 0;
            payButton.textContent = `Cobrar ${money(total)}`;

            if (cart.length === 0) {
                container.innerHTML = `<div class="cart-empty">No hay productos<br>Busca y agrega productos</div>`;
                return;
            }

            container.innerHTML = cart.map(item => `
                <div class="cart-item">
                    <div class="cart-item-top">
                        <div>
                            <div class="cart-item-name">${escapeHtml(item.name_product)}</div>
                            <div class="cart-item-controls">
                                <button class="qty-btn" type="button" onclick="decreaseQty(${item.product_id})">−</button>
                                <strong>${item.quantity}</strong>
                                <button class="qty-btn" type="button" onclick="increaseQty(${item.product_id})">+</button>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <button class="trash-btn" type="button" onclick="removeItem(${item.product_id})">🗑️</button>
                            <div style="font-weight:700; margin-top:10px;">${money(item.price * item.quantity)}</div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function renderProductsLoading() {
            const grid = document.getElementById('productsGrid');
            grid.innerHTML = `<div class="products-empty">Buscando productos...</div>`;
        }

        function renderProducts(items, searchText = '') {
            const grid = document.getElementById('productsGrid');
            const trimmedSearch = String(searchText || '').trim();

            if (trimmedSearch === '') {
                grid.innerHTML = ``;
                return;
            }

            if (trimmedSearch.length < 2) {
                grid.innerHTML = `
                    <div class="products-empty">
                        Escribe al menos 2 caracteres para buscar.
                    </div>
                `;
                return;
            }

            if (!items.length) {
                grid.innerHTML = `
                    <div class="products-empty">
                        No se encontraron productos con la búsqueda realizada.
                    </div>
                `;
                return;
            }

            grid.innerHTML = items.map(item => `
                <div class="product-card" onclick="addProduct(${Number(item.product_id)})">
                    <div class="product-name">${escapeHtml(item.name_product)}</div>
                    <div class="product-price">${money(item.price)}</div>
                    <div class="product-stock">Stock: ${escapeHtml(item.stocks)}</div>
                </div>
            `).join('');
        }

        function addProduct(productId) {
            const product = productsCache.find(p => Number(p.product_id) === Number(productId));
            if (!product) return;

            const existing = cart.find(item => Number(item.product_id) === Number(productId));

            if (existing) {
                if (existing.quantity < Number(product.stocks)) {
                    existing.quantity += 1;
                }
            } else {
                cart.push({
                    product_id: Number(product.product_id),
                    name_product: product.name_product,
                    price: Number(product.price),
                    max_stock: Number(product.stocks),
                    quantity: 1
                });
            }

            renderCart();
        }

        function increaseQty(productId) {
            const item = cart.find(x => x.product_id === Number(productId));
            if (!item) return;
            if (item.quantity < item.max_stock) item.quantity += 1;
            renderCart();
        }

        function decreaseQty(productId) {
            const item = cart.find(x => x.product_id === Number(productId));
            if (!item) return;

            item.quantity -= 1;
            if (item.quantity <= 0) {
                cart = cart.filter(x => x.product_id !== Number(productId));
            }

            renderCart();
        }

        function removeItem(productId) {
            cart = cart.filter(x => x.product_id !== Number(productId));
            renderCart();
        }

        async function clearCart() {
            if (!cart.length) return;

            if (currentPendingSaleId) {
                const confirmed = await showAppConfirm(
                    'Esta acción cancelará la venta en proceso. ¿Deseas continuar?',
                    'Cancelar venta en proceso',
                    'La venta pasará a estado CANCELADA'
                );

                if (!confirmed) return;

                await cancelCurrentSale(false);
                return;
            }

            cart = [];
            renderCart();
            resetPaymentDraftFields();
            applyPaymentMethodUI();
        }

        function renderShiftClosedSuccess(summary) {
            document.getElementById('shiftClosedUser').textContent = summary?.user_name ?? '-';
            document.getElementById('shiftClosedDuration').textContent = summary?.duration_text ?? '-';
            document.getElementById('shiftClosedSalesCount').textContent = summary?.sales_count ?? 0;
            document.getElementById('shiftClosedTotalSold').textContent = money(summary?.total_sold ?? 0);
        }

        function closeShiftClosedSuccessModal() {
            document.getElementById('shiftClosedSuccessOverlay').classList.remove('show');
        }

        function backToSystem() {
            window.location.href = '/ventas';
        }

        function restartShiftFlow() {
            closeShiftClosedSuccessModal();
            prepareShiftOpenModal();
            document.getElementById('shiftModalOverlay').classList.add('show');
        }

        async function loadStatus() {
            const { response, data } = await apiFetch('/api/sales/pos/status');

            if (!response.ok) {
                alert(data.message || 'No se pudo cargar el POS.');
                window.location.href = '/ventas';
                return;
            }

            document.getElementById('posBranchName').textContent = data.branch?.name_branch ?? 'Sucursal actual';
            document.getElementById('posUserName').textContent = data.user?.name_user ?? '{{ auth()->user()->name_user }}';

            if (!data.cash_session) {
                document.getElementById('cashModalOverlay').classList.add('show');
                return;
            }

            if (!data.active_shift && data.system_shift && Number(data.system_shift.userr_idfk) !== Number(data.user?.userr_id)) {
                alert('Ya existe un turno abierto por otro usuario. Debes esperar a que cierre su turno.');
                window.location.href = '/ventas';
                return;
            }

            if (!data.active_shift) {
                prepareShiftOpenModal();
                document.getElementById('shiftModalOverlay').classList.add('show');
                return;
            }

            productsCache = [];
            renderProducts([], '');
        }

        async function loadProducts() {
            const search = document.getElementById('posSearch').value.trim();
            const normalizedSearch = search.toLowerCase();
            const currentRequestId = ++productsRequestId;

            if (search === '') {
                productsCache = [];
                renderProducts([], '');
                return;
            }

            if (search.length < 2) {
                productsCache = [];
                renderProducts([], search);
                return;
            }

            if (productSearchMemory.has(normalizedSearch)) {
                productsCache = productSearchMemory.get(normalizedSearch);
                renderProducts(productsCache, search);
                return;
            }

            if (productsController) {
                productsController.abort();
            }

            productsController = new AbortController();
            renderProductsLoading();

            const { response, data, aborted } = await apiFetch(
                `/api/sales/pos/products?search=${encodeURIComponent(search)}&limit=20`,
                { signal: productsController.signal }
            );

            if (aborted || currentRequestId !== productsRequestId) return;

            if (!response.ok) {
                productsCache = [];
                renderProducts([], search);
                return;
            }

            productsCache = Array.isArray(data) ? data : [];
            productSearchMemory.set(normalizedSearch, productsCache);
            renderProducts(productsCache, search);
        }

        async function openCash() {
            if (!salesPosAccess.cash_open) return;
            if (isOpeningCash) return;

            hideError('cashErrorBox');

            const opening_amount = document.getElementById('openingAmount').value;
            const notes_opening = document.getElementById('openingNotes').value.trim();

            isOpeningCash = true;
            setButtonLoading('openCashButton', true, 'Abriendo...');

            try {
                const { response, data } = await apiFetch('/api/sales/cash/open', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ opening_amount, notes_opening })
                });

                if (!response.ok) {
                    showError(
                        'cashErrorBox',
                        data.errors?.cash_session?.[0] ||
                        data.errors?.opening_amount?.[0] ||
                        data.message ||
                        'No se pudo abrir la caja.'
                    );
                    return;
                }

                document.getElementById('cashModalOverlay').classList.remove('show');
                prepareShiftOpenModal();
                document.getElementById('shiftModalOverlay').classList.add('show');
            } finally {
                isOpeningCash = false;
                setButtonLoading('openCashButton', false, 'Abrir caja');
            }
        }

        async function openShift() {
            if (!salesPosAccess.pos_use) return;
            if (isOpeningShift) return;

            hideError('shiftErrorBox');

            const shift_type = document.getElementById('shiftType').value.trim() || 'CAJERO';

            isOpeningShift = true;
            setButtonLoading('openShiftButton', true, 'Iniciando...');

            try {
                const { response, data } = await apiFetch('/api/sales/shifts/open', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ shift_type })
                });

                if (!response.ok) {
                    showError(
                        'shiftErrorBox',
                        data.errors?.shift?.[0] ||
                        data.message ||
                        'No se pudo iniciar el turno.'
                    );
                    return;
                }

                document.getElementById('shiftModalOverlay').classList.remove('show');
                productsCache = [];
                renderProducts([], '');
            } finally {
                isOpeningShift = false;
                setButtonLoading('openShiftButton', false, 'Iniciar turno');
            }
        }

        async function openCloseShiftModal() {
            if (!salesPosAccess.pos_use) return;

            hideError('closeShiftErrorBox');
            document.getElementById('closeShiftNotes').value = '';

            const { response, data } = await apiFetch('/api/sales/shifts/summary');

            if (!response.ok) {
                alert(data.message || 'No se pudo cargar el resumen del turno.');
                return;
            }

            currentShiftSummary = data;

            document.getElementById('closeShiftUserName').textContent = data.user_name ?? '-';
            document.getElementById('closeShiftStartTime').textContent = data.start_time_label ?? '-';
            document.getElementById('closeShiftEndTime').textContent = data.end_time_label ?? '-';
            document.getElementById('closeShiftDuration').textContent = data.duration_text ?? '-';

            document.getElementById('closeShiftTotalSold').textContent = money(data.total_sold ?? 0);
            document.getElementById('closeShiftSalesCount').textContent = data.sales_count ?? 0;
            document.getElementById('closeShiftAvgTicket').textContent = money(data.avg_ticket ?? 0);

            document.getElementById('closeShiftCardCount').textContent = `${data.payment_methods?.TARJETA ?? 0} ventas`;
            document.getElementById('closeShiftCashCount').textContent = `${data.payment_methods?.EFECTIVO ?? 0} ventas`;
            document.getElementById('closeShiftTransferCount').textContent = `${data.payment_methods?.TRANSFERENCIA ?? 0} ventas`;

            document.getElementById('closeShiftOverlay').classList.add('show');
        }

        function closeCloseShiftModal() {
            document.getElementById('closeShiftOverlay').classList.remove('show');
        }

        async function confirmCloseShift() {
            if (!salesPosAccess.pos_use) return;
            if (isClosingShift) return;

            hideError('closeShiftErrorBox');

            const notes_shift = document.getElementById('closeShiftNotes').value.trim();

            isClosingShift = true;
            setButtonLoading('confirmCloseShiftButton', true, 'Cerrando...');

            try {
                const { response, data } = await apiFetch('/api/sales/shifts/close', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ notes_shift })
                });

                if (!response.ok) {
                    showError(
                        'closeShiftErrorBox',
                        data.errors?.shift?.[0] ||
                        data.message ||
                        'No se pudo cerrar el turno.'
                    );
                    return;
                }

                closeCloseShiftModal();

                if (typeof resetActiveSaleState === 'function') {
                    resetActiveSaleState();
                } else {
                    cart = [];
                    renderCart();
                    productsCache = [];
                    renderProducts([], '');
                    document.getElementById('posSearch').value = '';
                    clearSelectedCustomer();
                }

                renderShiftClosedSuccess(currentShiftSummary || {
                    user_name: document.getElementById('posUserName').textContent.trim(),
                    duration_text: '-',
                    sales_count: 0,
                    total_sold: 0
                });

                document.getElementById('shiftClosedSuccessOverlay').classList.add('show');
            } finally {
                isClosingShift = false;
                setButtonLoading('confirmCloseShiftButton', false, 'Confirmar cierre');
            }
        }

        function closeCash() {
            if (!salesPosAccess.cash_close) return;

            hideError('closeCashErrorBox');

            document.getElementById('closeCashUserName').textContent =
                document.getElementById('posUserName').textContent.trim() || 'Usuario';

            document.getElementById('closeCashBranchName').textContent =
                document.getElementById('posBranchName').textContent.trim() || 'Sucursal actual';

            document.getElementById('closeCashAmount').value = '';
            document.getElementById('closeCashNotes').value = '';

            document.getElementById('closeCashOverlay').classList.add('show');
        }

        function closeCashModal() {
            document.getElementById('closeCashOverlay').classList.remove('show');
        }

        async function confirmCloseCash() {
            if (!salesPosAccess.cash_close) return;
            if (isClosingCash) return;

            hideError('closeCashErrorBox');

            const closingAmount = document.getElementById('closeCashAmount').value.trim();
            const notesClosing = document.getElementById('closeCashNotes').value.trim();

            if (closingAmount === '' || Number(closingAmount) < 0) {
                showError('closeCashErrorBox', 'Ingresa un monto final válido.');
                return;
            }

            isClosingCash = true;
            setButtonLoading('confirmCloseCashButton', true, 'Cerrando...');

            try {
                const { response, data } = await apiFetch('/api/sales/cash/close', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        closing_amount: closingAmount,
                        notes_closing: notesClosing
                    })
                });

                if (!response.ok) {
                    showError(
                        'closeCashErrorBox',
                        data.errors?.shift?.[0] ||
                        data.errors?.closing_amount?.[0] ||
                        data.message ||
                        'No se pudo cerrar la caja.'
                    );
                    return;
                }

                closeCashModal();
                window.location.href = data.redirect_url || ('/ventas/cajas/' + data.cash_session_id);
            } finally {
                isClosingCash = false;
                setButtonLoading('confirmCloseCashButton', false, 'Confirmar cierre');
            }
        }

        function applyPaymentMethodUI() {
            const isCash = paymentMethod === 'EFECTIVO';
            const isCard = paymentMethod === 'TARJETA';
            const isTransfer = paymentMethod === 'TRANSFERENCIA';

            document.getElementById('methodEFECTIVO').classList.toggle('active', isCash);
            document.getElementById('methodTARJETA').classList.toggle('active', isCard);
            document.getElementById('methodTRANSFERENCIA').classList.toggle('active', isTransfer);

            document.getElementById('cashFields').style.display = isCash ? 'block' : 'none';
            document.getElementById('referenceWrap').style.display = isCash ? 'none' : 'block';
            document.getElementById('cardLast4Wrap').style.display = isCard ? 'block' : 'none';
        }


        async function openPaymentModal() {
            if (!cart.length || isConfirmingSale) return;

            hideError('paymentErrorBox');

            const pendingResult = await savePendingSale(false);

            if (!pendingResult.ok) {
                const message =
                    pendingResult.data?.errors?.items?.[0] ||
                    pendingResult.data?.errors?.customer_id?.[0] ||
                    pendingResult.data?.errors?.amount_paid?.[0] ||
                    pendingResult.data?.errors?.sale?.[0] ||
                    pendingResult.data?.error ||
                    pendingResult.data?.message ||
                    'No se pudo preparar la venta pendiente.';

                showAppAlert(message, 'Error', 'No se pudo preparar la venta');
                return;
            }

            document.getElementById('amountPaid').value = '';
            document.getElementById('paymentBank').value = '';
            document.getElementById('cardLast4').value = '';
            document.getElementById('referencePayment').value = '';

            paymentMethod = 'EFECTIVO';
            document.getElementById('paymentTotalText').textContent = money(cartTotal());
            document.getElementById('paymentCustomerText').textContent = selectedCustomer?.name_customer || 'Cliente general';

            applyPaymentMethodUI();
            renderPaymentTotals();

            document.getElementById('paymentModalOverlay').classList.add('show');
        }

        async function closePaymentModal() {
            hideError('paymentErrorBox');

            if (cart.length) {
                const pendingResult = await savePendingSale(false);

                if (!pendingResult.ok) {
                    showError('paymentErrorBox', 'No se pudo guardar la venta como pendiente.');
                    return;
                }
            }

            document.getElementById('paymentModalOverlay').classList.remove('show');
            resetActiveSaleState();
        }

        function selectMethod(method) {
            paymentMethod = method;
            applyPaymentMethodUI();

            const cardLast4Wrap = document.getElementById('cardLast4Wrap');
            const referencePaymentLabel = document.getElementById('referencePaymentLabel');
            const referencePayment = document.getElementById('referencePayment');
            const electronicHelp = document.getElementById('electronicSimulationHelp');

            if (method === 'EFECTIVO') {
                document.getElementById('cashFields').style.display = 'block';
                document.getElementById('referenceWrap').style.display = 'none';
            } else {
                document.getElementById('cashFields').style.display = 'none';
                document.getElementById('referenceWrap').style.display = 'block';

                if (method === 'TARJETA') {
                    cardLast4Wrap.style.display = 'block';
                    referencePaymentLabel.textContent = 'Código de autorización';
                    referencePayment.placeholder = 'Ej. APROBADA-001';
                    electronicHelp.innerHTML = `
                        <strong>Simulación local:</strong>
                        Para probar rechazo usa <strong>RECHAZADA</strong>, <strong>DECLINED</strong>,
                        <strong>FAIL</strong>, <strong>ERROR</strong> o <strong>DENIED</strong> en el código.
                        También se rechaza con tarjeta terminación <strong>0000</strong>,
                        <strong>1111</strong> o <strong>9999</strong>.
                    `;
                }

                if (method === 'TRANSFERENCIA') {
                    cardLast4Wrap.style.display = 'none';
                    referencePaymentLabel.textContent = 'Referencia de transferencia';
                    referencePayment.placeholder = 'Ej. TRX-000123';
                    electronicHelp.innerHTML = `
                        <strong>Simulación local:</strong>
                        Para probar rechazo usa <strong>RECHAZADA</strong>, <strong>DECLINED</strong>,
                        <strong>FAIL</strong>, <strong>ERROR</strong> o <strong>DENIED</strong> en la referencia.
                    `;
                }
            }

            renderPaymentTotals();
        }

        function renderPaymentTotals() {
            const total = cartTotal();
            let change = 0;
            let canConfirm = true;

            if (paymentMethod === 'EFECTIVO') {
                const amountPaid = Number(document.getElementById('amountPaid').value || 0);
                change = Math.max(amountPaid - total, 0);
                canConfirm = amountPaid >= total && total > 0;
            } else {
                canConfirm = total > 0;
            }

            document.getElementById('changeGivenText').textContent = money(change);
            document.getElementById('paymentTotalText').textContent = money(total);
            document.getElementById('confirmSaleButton').disabled = !canConfirm || isConfirmingSale;
        }

        async function cancelCurrentSale(showMessage = true) {
            if (isCancellingSale) return;

            hideError('paymentErrorBox');

            if (!currentPendingSaleId) {
                document.getElementById('paymentModalOverlay').classList.remove('show');
                resetActiveSaleState();
                return;
            }

            const confirmed = await showAppConfirm(
                '¿Deseas cancelar esta venta pendiente?',
                'Cancelar venta',
                'Esta acción cambiará el estado a CANCELADA'
            );

            if (!confirmed) return;

            isCancellingSale = true;
            setButtonLoading('cancelSaleButton', true, 'Cancelando...');

            try {
                const { response, data } = await apiFetch(`/api/sales/${currentPendingSaleId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (!response.ok) {
                    showError(
                        'paymentErrorBox',
                        data.errors?.sale?.[0] ||
                        data.message ||
                        'No se pudo cancelar la venta.'
                    );
                    return;
                }

                document.getElementById('paymentModalOverlay').classList.remove('show');
                resetActiveSaleState();

                if (showMessage) {
                    showAppAlert(
                        'La venta pendiente fue cancelada correctamente.',
                        'Venta cancelada',
                        'El registro quedó actualizado en el historial'
                    );
                }
            } finally {
                isCancellingSale = false;
                setButtonLoading('cancelSaleButton', false, 'Cancelando...');
            }
        }

        async function confirmSale() {
            if (!salesPosAccess.create) return;
            if (isConfirmingSale) return;

            hideError('paymentErrorBox');

            if (!cart.length) {
                showError('paymentErrorBox', 'Agrega al menos un producto.');
                return;
            }

            if (!currentPendingSaleId) {
                const pendingResult = await savePendingSale();

                if (!pendingResult.ok) {
                    return;
                }
            }

            if (paymentMethod !== 'EFECTIVO') {
                const electronicValidationError = validateElectronicFields();

                if (electronicValidationError) {
                    showError('paymentErrorBox', electronicValidationError);
                    return;
                }
            }

            const payload = buildSalePayload();

            isConfirmingSale = true;
            setButtonLoading(
                'confirmSaleButton',
                true,
                paymentMethod === 'EFECTIVO' ? 'Procesando...' : 'Autorizando...'
            );

            try {
                if (paymentMethod !== 'EFECTIVO') {
                    const simulation = await simulateElectronicAuthorization();

                    if (!simulation.approved) {
                        await cancelSaleAfterRejectedPayment(simulation.reason);
                        return;
                    }
                }

                const { response, data } = await apiFetch(`/api/sales/${currentPendingSaleId}/confirm`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    showError(
                        'paymentErrorBox',
                        data.errors?.amount_paid?.[0] ||
                        data.errors?.customer_id?.[0] ||
                        data.errors?.items?.[0] ||
                        data.errors?.shift?.[0] ||
                        data.message ||
                        'No se pudo registrar la venta.'
                    );
                    return;
                }

                document.getElementById('paymentModalOverlay').classList.remove('show');

                const sale = data.data || data;
                const customerName = selectedCustomer?.name_customer || 'Cliente general';

                lastSaleId = sale.sale_id;
                currentPendingSaleId = null;

                document.getElementById('successInfo').innerHTML = `
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Folio de venta</span><strong>${escapeHtml(sale.sale_folio)}</strong></div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Cliente</span><strong>${escapeHtml(customerName)}</strong></div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Método de pago</span><strong>${escapeHtml(sale.payment_method)}</strong></div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Productos</span><strong>${escapeHtml(sale.items_count)} items</strong></div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Recibido</span><strong>${money(sale.amount_paid)}</strong></div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Cambio</span><strong>${money(sale.change_given)}</strong></div>
                    <div style="display:flex; justify-content:space-between;"><span>Total</span><strong>${money(sale.total)}</strong></div>
                `;

                document.getElementById('successItems').innerHTML = `
                    <div style="font-weight:700; margin-bottom:10px;">PRODUCTOS VENDIDOS</div>
                    ${sale.items.map(item => `
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span>${escapeHtml(item.quantity)}x ${escapeHtml(item.product_name)}</span>
                            <strong>${money(item.line_subtotal)}</strong>
                        </div>
                    `).join('')}
                `;

                if (salesPosAccess.ticket_print) {
                    const ticketUrl = '/ventas/' + lastSaleId + '/ticket';

                    const ticketButton = document.getElementById('viewTicketBtn');
                    if (ticketButton) {
                        ticketButton.onclick = function () {
                            window.open(ticketUrl, '_blank');
                        };
                    }

                    if (posPrintConfig.autoPrint) {
                        window.open(ticketUrl + '?print=1', '_blank', 'width=480,height=760');
                    }
                }

                if (typeof resetActiveSaleState === 'function') {
                    resetActiveSaleState();
                } else {
                    cart = [];
                    renderCart();
                    productsCache = [];
                    renderProducts([], '');
                    document.getElementById('posSearch').value = '';
                    clearSelectedCustomer();
                }

                document.getElementById('successModalOverlay').classList.add('show');
            } finally {
                isConfirmingSale = false;
                setButtonLoading(
                    'confirmSaleButton',
                    false,
                    paymentMethod === 'EFECTIVO' ? 'Procesando...' : 'Autorizando...'
                );
                renderPaymentTotals();
            }
        }
    
        function startNewSale() {
            document.getElementById('successModalOverlay').classList.remove('show');
            resetActiveSaleState();
        }

        function getSaleIdFromUrl() {
            const params = new URLSearchParams(window.location.search);
            const saleId = params.get('sale_id');

            if (!saleId) return null;

            const numericId = Number(saleId);
            return Number.isInteger(numericId) && numericId > 0 ? numericId : null;
        }

        async function loadPendingSaleFromUrl() {
            const saleId = getSaleIdFromUrl();

            if (!saleId) return;

            const { response, data } = await apiFetch(`/api/sales/${saleId}`);

            if (!response.ok) {
                showAppAlert(
                    data.message || 'No se pudo cargar la venta pendiente.',
                    'Error',
                    'No fue posible recuperar la venta'
                );
                return;
            }

            if (data.status_sale !== 'PENDIENTE') {
                showAppAlert(
                    'La venta seleccionada ya no está pendiente.',
                    'Venta no disponible',
                    'El estado de la venta cambió'
                );
                return;
            }

            currentPendingSaleId = Number(data.sale_id);

            if (data.customer && data.customer.customer_id) {
                const customerName = String(data.customer.name_customer || '').trim().toLowerCase();

                if (customerName === 'cliente general') {
                    selectedCustomer = null;
                    document.getElementById('customerSearch').value = '';
                } else {
                    selectedCustomer = {
                        customer_id: Number(data.customer.customer_id),
                        name_customer: data.customer.name_customer,
                        phone: data.customer.phone || '',
                        email: data.customer.email || ''
                    };
                    document.getElementById('customerSearch').value = data.customer.name_customer || '';
                }
            } else {
                selectedCustomer = null;
                document.getElementById('customerSearch').value = '';
            }

            cart = (data.items || [])
                .filter(item => item.item_type === 'PRODUCTO')
                .map(item => ({
                    product_id: Number(item.item_id),
                    name_product: item.item_name,
                    price: Number(item.unit_price),
                    max_stock: 999999,
                    quantity: Number(item.amount)
                }));

            paymentMethod = data.payment?.payment_method || 'EFECTIVO';

            document.getElementById('amountPaid').value =
                paymentMethod === 'EFECTIVO'
                    ? Number(data.payment?.amount_paid || 0) || ''
                    : '';

            document.getElementById('referencePayment').value =
                paymentMethod !== 'EFECTIVO'
                    ? (data.payment?.reference_payment || '')
                    : '';

            renderSelectedCustomer();
            renderCart();
            applyPaymentMethodUI();
            renderPaymentTotals();

            showAppAlert(
                `La venta pendiente <strong>${data.sale_folio}</strong> se recuperó correctamente.`,
                'Venta recuperada',
                'Puedes continuar con el cobro o modificarla'
            );

            window.history.replaceState({}, document.title, window.location.pathname);
        }

        document.addEventListener('DOMContentLoaded', async function () {
            renderCart();
            renderSelectedCustomer();
            applyPaymentMethodUI();
            await loadStatus();
            await loadPendingSaleFromUrl();

            const posSearch = document.getElementById('posSearch');
            const customerSearch = document.getElementById('customerSearch');
            const customerResults = document.getElementById('customerResults');
            const optionsButton = document.getElementById('optionsButton');
            const optionsMenu = document.getElementById('optionsMenu');
            const closeCashOverlay = document.getElementById('closeCashOverlay');
            const amountPaid = document.getElementById('amountPaid');
            const referencePayment = document.getElementById('referencePayment');
            const paymentBank = document.getElementById('paymentBank');
            const cardLast4 = document.getElementById('cardLast4');

            const debouncedLoadProducts = debounce(loadProducts, 350);
            const debouncedLoadCustomers = debounce(loadCustomers, 300);

            posSearch.addEventListener('input', debouncedLoadProducts);

            customerSearch.addEventListener('input', debouncedLoadCustomers);
            customerSearch.addEventListener('focus', loadCustomers);

            amountPaid.addEventListener('input', renderPaymentTotals);

            referencePayment.addEventListener('input', function () {
                if (currentPendingSaleId && document.getElementById('paymentModalOverlay').classList.contains('show')) {
                    savePendingSale(false);
                }
            });

            paymentBank.addEventListener('change', function () {
                if (currentPendingSaleId && document.getElementById('paymentModalOverlay').classList.contains('show')) {
                    savePendingSale(false);
                }
            });

            cardLast4.addEventListener('input', function () {
                if (currentPendingSaleId && document.getElementById('paymentModalOverlay').classList.contains('show')) {
                    savePendingSale(false);
                }
            });

            optionsButton.addEventListener('click', function () {
                optionsMenu.classList.toggle('show');
            });

            document.addEventListener('click', function (e) {
                if (!optionsButton.contains(e.target) && !optionsMenu.contains(e.target)) {
                    optionsMenu.classList.remove('show');
                }

                if (!customerSearch.contains(e.target) && !customerResults.contains(e.target)) {
                    hideCustomerResults();
                }
            });

            closeCashOverlay.addEventListener('click', function (e) {
                if (e.target === closeCashOverlay) {
                    closeCashModal();
                }
            });
        });
</script>
</body>

<script>
(function () {
    const savedTheme =
        localStorage.getItem('theme') ||
        localStorage.getItem('punto_theme') ||
        document.body.getAttribute('data-theme') ||
        'light';

    const theme = savedTheme === 'dark' ? 'dark' : 'light';

    document.documentElement.setAttribute('data-theme', theme);
    document.body.setAttribute('data-theme', theme);
})();
</script>
</html>