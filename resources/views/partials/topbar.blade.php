@php
    $branchContext = $topbarBranchContext ?? [
        'can_switch' => false,
        'branches' => collect(),
        'current_branch' => null,
    ];

    $branchCreateContext = $topbarBranchCreateContext ?? [
        'can_create' => false,
        'manager_users' => collect(),
        'branch_count' => 0,
        'is_first_branch' => false,
        'has_managers' => false,
    ];

    $currentBranch = $branchContext['current_branch'];
    $canSwitchBranch = $branchContext['can_switch'];
    $canCreateBranch = $branchCreateContext['can_create'];
    $canOpenBranchDropdown = $canSwitchBranch || $canCreateBranch;

    $branchCount = (int) ($branchCreateContext['branch_count'] ?? 0);
    $isFirstBranch = (bool) ($branchCreateContext['is_first_branch'] ?? false);
    $hasManagers = (bool) ($branchCreateContext['has_managers'] ?? false);

    $roleName = \App\Support\UserAccess::roleName(auth()->user());
    $displayRole = match ($roleName) {
        'ADMINISTRADOR' => 'Administrador',
        'GERENTE' => 'Gerente',
        'CAJERO' => 'Cajero',
        default => 'Sin rol',
    };
@endphp

<header class="topbar">
    <!-- <div class="topbar-left">
        <div class="global-search" id="globalSearch">
            <input
                type="text"
                id="globalSearchInput"
                class="global-search-input"
                placeholder="Buscar productos, servicios, clientes, ventas..."
                autocomplete="off"
            >

            <div class="global-search-dropdown" id="globalSearchDropdown">
                <div class="global-search-results" id="globalSearchResults">
                    <div class="global-search-empty">
                        Escribe al menos 2 letras para buscar.
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <div class="topbar-right">
        <div class="notification-wrapper" id="notificationWrapper">
            <button type="button" class="notification-button" id="notificationButton">
                <span class="notification-icon">🔔</span>
                <span class="notification-count" id="notificationCount" style="display:none;">0</span>
            </button>

            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-dropdown-header">
                    <div>
                        <strong>Notificaciones</strong>
                        <span>Últimas alertas del negocio</span>
                    </div>

                    <div class="notification-header-actions">
                        <button type="button" id="markAllNotificationsRead">
                            Marcar leídas
                        </button>

                        <button type="button" id="deleteReadNotifications">
                            Borrar leídas
                        </button>
                    </div>
                </div>

                <div class="notification-dropdown-list" id="notificationList">
                    <div class="notification-empty">
                        Cargando notificaciones...
                    </div>
                </div>
            </div>
        </div>

        <div class="branch-selector">
            @if($canOpenBranchDropdown)
                <button type="button" class="branch-button" id="branchButton">
                    <span class="branch-label">Sucursal actual</span>
                    <span class="branch-name" id="currentBranchName">
                        {{ $currentBranch?->name_branch ?? 'Sin sucursal' }}
                    </span>
                </button>

                <div class="branch-dropdown" id="branchDropdown">
                    <div id="branchDropdownList">
                        @if($canSwitchBranch && $branchContext['branches']->count())
                            @foreach($branchContext['branches'] as $branch)
                                <form method="POST" action="{{ route('current-branch.update') }}">
                                    @csrf
                                    <input type="hidden" name="branch_id" value="{{ $branch->branch_id }}">
                                    <button
                                        type="submit"
                                        class="branch-option {{ (int) optional($currentBranch)->branch_id === (int) $branch->branch_id ? 'active' : '' }}"
                                    >
                                        {{ $branch->name_branch }}
                                    </button>
                                </form>
                            @endforeach
                        @else
                            <div class="branch-empty">No hay sucursales disponibles</div>
                        @endif
                    </div>

                    @if($canCreateBranch)
                        <div class="branch-dropdown-divider"></div>

                        <button type="button" class="branch-create-link" id="openCreateBranchModal">
                            + Crear nueva sucursal
                        </button>
                    @endif
                </div>
            @else
                <div class="branch-button static">
                    <span class="branch-label">Sucursal actual</span>
                    <span class="branch-name">
                        {{ $currentBranch?->name_branch ?? 'Sin sucursal' }}
                    </span>
                </div>
            @endif
        </div>

        <div class="user-box">
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name_user }}</div>
                <div class="user-role">{{ $displayRole }}</div>
            </div>

            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->name_user, 0, 1)) }}
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn">Cerrar sesión</button>
            </form>
        </div>
    </div>
</header>

@if($canCreateBranch)
<div id="branchModalOverlay" class="branch-modal-overlay">
    <div class="branch-modal">
        <div class="branch-modal-header">
            <div class="branch-modal-icon">🏬</div>

            <div class="branch-modal-title-wrap">
                <h2 class="branch-modal-title">Crear nueva sucursal</h2>
                <p class="branch-modal-subtitle" id="branchModalSubtitle">
                    {{ $isFirstBranch
                        ? 'Esta será la primera sucursal del negocio y se asignará automáticamente al owner.'
                        : 'Selecciona el gerente responsable de la nueva sucursal.' }}
                </p>
            </div>

            <button type="button" class="branch-modal-close" id="closeCreateBranchModal">×</button>
        </div>

        <div class="branch-modal-body">
            <form id="createBranchForm">
                @csrf

                <div class="branch-section">
                    <div class="branch-section-title">Información básica</div>

                    <label class="branch-field-label" for="name_branch">Nombre de la sucursal *</label>
                    <input type="text" id="name_branch" name="name_branch" class="branch-field-input" placeholder="Ej: Sucursal Plaza Norte" maxlength="50" required>
                </div>

                <div class="branch-section">
                    <div class="branch-section-title">Ubicación</div>

                    <label class="branch-field-label" for="address">Dirección *</label>
                    <input type="text" id="address" name="address" class="branch-field-input" placeholder="Calle, número, colonia" maxlength="150" required>

                    <label class="branch-field-label" for="city">Ciudad *</label>
                    <input type="text" id="city" name="city" class="branch-field-input" placeholder="Ej: Ciudad de México" maxlength="100" required>

                    <label class="branch-field-label" for="state">Estado *</label>
                    <input type="text" id="state" name="state" class="branch-field-input" placeholder="Ej: CDMX" maxlength="100" required>

                    <label class="branch-field-label" for="phone">Teléfono</label>
                    <input type="text" id="phone" name="phone" class="branch-field-input" placeholder="5512345678" maxlength="10">
                </div>

                <div class="branch-section" id="branchResponsibleSection">
                    <div class="branch-section-title">Responsable de la sucursal</div>

                    <label class="branch-field-label" for="responsible_user_id">Gerente responsable *</label>
                    <select id="responsible_user_id" name="responsible_user_id" class="branch-field-input">
                        <option value="">Selecciona un gerente</option>
                        @foreach($branchCreateContext['manager_users'] as $manager)
                            <option value="{{ $manager->userr_id }}">
                                {{ $manager->name_user }}{{ $manager->email ? ' - ' . $manager->email : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="branch-note" id="branchOwnerNote" style="{{ $isFirstBranch ? '' : 'display:none;' }}">
                    <strong>Nota:</strong> Como es la primera sucursal del negocio, se asignará automáticamente al owner actual.
                </div>

                <div class="branch-note" id="branchManagerRequiredNote" style="{{ !$isFirstBranch ? '' : 'display:none;' }}">
                    <strong>Nota:</strong> A partir de la segunda sucursal debes seleccionar un gerente responsable.
                </div>

                <div class="branch-note" id="branchNoManagersNote" style="{{ (!$isFirstBranch && !$hasManagers) ? '' : 'display:none;' }}">
                    <strong>Atención:</strong> Primero debes crear al menos un usuario con rol gerente para poder registrar otra sucursal.
                </div>

                <div id="branchFormMessage" class="branch-form-message" style="display: none;"></div>

                <div class="branch-modal-footer">
                    <button type="button" class="branch-cancel-btn" id="cancelCreateBranchModal">Cancelar</button>
                    <button
                        type="submit"
                        class="branch-submit-btn"
                        id="submitCreateBranch"
                        {{ (!$isFirstBranch && !$hasManagers) ? 'disabled' : '' }}
                    >
                        <span id="submitCreateBranchText">Crear sucursal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($canOpenBranchDropdown || $canCreateBranch)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchButton = document.getElementById('branchButton');
    const branchDropdown = document.getElementById('branchDropdown');

    const openCreateBranchModal = document.getElementById('openCreateBranchModal');
    const closeCreateBranchModal = document.getElementById('closeCreateBranchModal');
    const cancelCreateBranchModal = document.getElementById('cancelCreateBranchModal');
    const branchModalOverlay = document.getElementById('branchModalOverlay');
    const createBranchForm = document.getElementById('createBranchForm');
    const branchFormMessage = document.getElementById('branchFormMessage');
    const submitCreateBranch = document.getElementById('submitCreateBranch');
    const submitCreateBranchText = document.getElementById('submitCreateBranchText');
    const responsibleSection = document.getElementById('branchResponsibleSection');
    const responsibleSelect = document.getElementById('responsible_user_id');
    const branchOwnerNote = document.getElementById('branchOwnerNote');
    const branchManagerRequiredNote = document.getElementById('branchManagerRequiredNote');
    const branchNoManagersNote = document.getElementById('branchNoManagersNote');
    const branchModalSubtitle = document.getElementById('branchModalSubtitle');

    const isFirstBranch = @json($isFirstBranch);
    const hasManagers = @json($hasManagers);

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    if (branchButton && branchDropdown) {
        branchButton.addEventListener('click', function (e) {
            e.stopPropagation();
            branchDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            if (!branchButton.contains(e.target) && !branchDropdown.contains(e.target)) {
                branchDropdown.classList.remove('show');
            }
        });
    }

    function applyBranchModalMode() {
        if (responsibleSection) {
            responsibleSection.style.display = isFirstBranch ? 'none' : 'block';
        }

        if (responsibleSelect) {
            responsibleSelect.required = !isFirstBranch;
            if (isFirstBranch) {
                responsibleSelect.value = '';
            }
        }

        if (branchOwnerNote) {
            branchOwnerNote.style.display = isFirstBranch ? 'block' : 'none';
        }

        if (branchManagerRequiredNote) {
            branchManagerRequiredNote.style.display = !isFirstBranch ? 'block' : 'none';
        }

        if (branchNoManagersNote) {
            branchNoManagersNote.style.display = (!isFirstBranch && !hasManagers) ? 'block' : 'none';
        }

        if (branchModalSubtitle) {
            branchModalSubtitle.textContent = isFirstBranch
                ? 'Esta será la primera sucursal del negocio y se asignará automáticamente al owner.'
                : 'Selecciona el gerente responsable de la nueva sucursal.';
        }

        if (submitCreateBranch) {
            submitCreateBranch.disabled = !isFirstBranch && !hasManagers;
        }
    }

    function resetBranchMessage() {
        if (!branchFormMessage) return;
        branchFormMessage.style.display = 'none';
        branchFormMessage.textContent = '';
        branchFormMessage.className = 'branch-form-message';
    }

    function openModal() {
        if (!branchModalOverlay) return;
        branchModalOverlay.classList.add('show');
        if (branchDropdown) branchDropdown.classList.remove('show');
        if (createBranchForm) createBranchForm.reset();
        resetBranchMessage();
        applyBranchModalMode();
    }

    function closeModal() {
        if (!branchModalOverlay) return;
        branchModalOverlay.classList.remove('show');
        if (createBranchForm) createBranchForm.reset();
        resetBranchMessage();
        applyBranchModalMode();
    }

    function showError(message) {
        if (!branchFormMessage) return;
        branchFormMessage.textContent = message;
        branchFormMessage.className = 'branch-form-message error';
        branchFormMessage.style.display = 'block';
    }

    function showSuccess(message) {
        if (!branchFormMessage) return;
        branchFormMessage.textContent = message;
        branchFormMessage.className = 'branch-form-message success';
        branchFormMessage.style.display = 'block';
    }

    if (openCreateBranchModal) openCreateBranchModal.addEventListener('click', openModal);
    if (closeCreateBranchModal) closeCreateBranchModal.addEventListener('click', closeModal);
    if (cancelCreateBranchModal) cancelCreateBranchModal.addEventListener('click', closeModal);

    if (branchModalOverlay) {
        branchModalOverlay.addEventListener('click', function (e) {
            if (e.target === branchModalOverlay) closeModal();
        });
    }

    if (createBranchForm) {
        createBranchForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            resetBranchMessage();
            applyBranchModalMode();

            if (!isFirstBranch && !hasManagers) {
                showError('Primero debes crear al menos un gerente.');
                return;
            }

            const formData = new FormData(createBranchForm);
            const payload = {
                name_branch: (formData.get('name_branch') || '').trim(),
                address: (formData.get('address') || '').trim(),
                city: (formData.get('city') || '').trim(),
                state: (formData.get('state') || '').trim(),
                phone: (formData.get('phone') || '').trim(),
                responsible_user_id: isFirstBranch ? null : (formData.get('responsible_user_id') || null)
            };

            submitCreateBranch.disabled = true;
            submitCreateBranchText.textContent = 'Creando...';

            try {
                const response = await fetch('{{ route('branches.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (data.errors) {
                        const errors = Object.values(data.errors).flat().join(' ');
                        showError(errors || 'No se pudo crear la sucursal.');
                    } else {
                        showError(data.message || 'No se pudo crear la sucursal.');
                    }

                    applyBranchModalMode();
                    submitCreateBranchText.textContent = 'Crear sucursal';
                    submitCreateBranch.disabled = false;
                    return;
                }

                showSuccess(data.message || 'Sucursal creada correctamente.');

                setTimeout(() => {
                    window.location.reload();
                }, 700);
            } catch (error) {
                showError('Ocurrió un error al crear la sucursal.');
                applyBranchModalMode();
                submitCreateBranchText.textContent = 'Crear sucursal';
                submitCreateBranch.disabled = false;
            }
        });
    }

    applyBranchModalMode();
});
</script>

<!--<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchWrapper = document.getElementById('globalSearch');
    const searchInput = document.getElementById('globalSearchInput');
    const searchDropdown = document.getElementById('globalSearchDropdown');
    const searchResults = document.getElementById('globalSearchResults');

    if (!searchWrapper || !searchInput || !searchDropdown || !searchResults) {
        return;
    }

    const searchRoute = @json(route('global-search'));
    let debounceTimer = null;
    let currentRequest = null;
    let currentItems = [];
    let activeIndex = -1;

    const badgeLabels = {
        products: 'Producto',
        services: 'Servicio',
        categories: 'Categoría',
        customers: 'Cliente',
        sales: 'Venta',
    };

    const icons = {
        products: '📦',
        services: '🛠️',
        categories: '🏷️',
        customers: '👤',
        sales: '🧾',
    };

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function openSearchDropdown() {
        searchDropdown.classList.add('show');
    }

    function closeSearchDropdown() {
        searchDropdown.classList.remove('show');
        activeIndex = -1;
        currentItems = [];
    }

    function setLoading() {
        searchResults.innerHTML = `
            <div class="global-search-loading">
                Buscando...
            </div>
        `;
        openSearchDropdown();
    }

    function setEmpty(message = 'No se encontraron resultados.') {
        searchResults.innerHTML = `
            <div class="global-search-empty">
                ${escapeHtml(message)}
            </div>
        `;
        currentItems = [];
        activeIndex = -1;
        openSearchDropdown();
    }

    function renderResults(groups) {
        if (!Array.isArray(groups) || groups.length === 0) {
            setEmpty('No se encontraron resultados.');
            return;
        }

        let html = '';
        const flatItems = [];

        groups.forEach((group) => {
            const items = Array.isArray(group.items) ? group.items : [];

            if (!items.length) return;

            html += `
                <div class="global-search-group">
                    <div class="global-search-group-title">${escapeHtml(group.group || 'Resultados')}</div>
            `;

            items.forEach((item) => {
                const index = flatItems.length;
                flatItems.push(item);

                html += `
                    <a href="${escapeHtml(item.url || '#')}" class="global-search-item" data-index="${index}">
                        <div class="global-search-item-icon">
                            ${escapeHtml(icons[item.type] || '🔎')}
                        </div>

                        <div class="global-search-item-body">
                            <div class="global-search-item-top">
                                <span class="global-search-item-title">${escapeHtml(item.title || 'Sin título')}</span>
                                <span class="global-search-badge ${escapeHtml(item.type || '')}">
                                    ${escapeHtml(badgeLabels[item.type] || 'Resultado')}
                                </span>
                            </div>

                            <div class="global-search-item-subtitle">
                                ${escapeHtml(item.subtitle || '')}
                            </div>

                            <div class="global-search-item-meta">
                                ${escapeHtml(item.meta || '')}
                            </div>
                        </div>
                    </a>
                `;
            });

            html += `</div>`;
        });

        if (!flatItems.length) {
            setEmpty('No se encontraron resultados.');
            return;
        }

        html += `
            <div class="global-search-footer">
                Usa ↑ ↓ para navegar y Enter para abrir el resultado.
            </div>
        `;

        searchResults.innerHTML = html;
        currentItems = flatItems;
        activeIndex = -1;
        openSearchDropdown();
    }

    function updateActiveItem() {
        const nodes = Array.from(searchResults.querySelectorAll('.global-search-item'));

        nodes.forEach((node, index) => {
            node.classList.toggle('active', index === activeIndex);
        });

        if (activeIndex >= 0 && nodes[activeIndex]) {
            nodes[activeIndex].scrollIntoView({
                block: 'nearest',
            });
        }
    }

    async function runSearch(query) {
        const cleanQuery = (query || '').trim();

        if (cleanQuery.length < 2) {
            setEmpty('Escribe al menos 2 letras para buscar.');
            return;
        }

        if (currentRequest) {
            currentRequest.abort();
        }

        currentRequest = new AbortController();
        setLoading();

        try {
            const response = await fetch(`${searchRoute}?q=${encodeURIComponent(cleanQuery)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: currentRequest.signal
            });

            if (!response.ok) {
                setEmpty('No se pudo realizar la búsqueda.');
                return;
            }

            const data = await response.json();
            renderResults(data.results || []);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            setEmpty('Ocurrió un error al buscar.');
        }
    }

    searchInput.addEventListener('focus', function () {
        const value = this.value.trim();

        if (value.length >= 2) {
            openSearchDropdown();
        }
    });

    searchInput.addEventListener('input', function () {
        const value = this.value.trim();

        clearTimeout(debounceTimer);

        if (value.length < 2) {
            setEmpty('Escribe al menos 2 letras para buscar.');
            return;
        }

        debounceTimer = setTimeout(() => {
            runSearch(value);
        }, 250);
    });

    searchInput.addEventListener('keydown', function (event) {
        const items = Array.from(searchResults.querySelectorAll('.global-search-item'));

        if (!searchDropdown.classList.contains('show') || !items.length) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            activeIndex = activeIndex < items.length - 1 ? activeIndex + 1 : 0;
            updateActiveItem();
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            activeIndex = activeIndex > 0 ? activeIndex - 1 : items.length - 1;
            updateActiveItem();
        }

        if (event.key === 'Enter' && activeIndex >= 0 && items[activeIndex]) {
            event.preventDefault();
            window.location.href = items[activeIndex].getAttribute('href');
        }

        if (event.key === 'Escape') {
            closeSearchDropdown();
        }
    });

    document.addEventListener('click', function (event) {
        if (!searchWrapper.contains(event.target)) {
            closeSearchDropdown();
        }
    });

    searchDropdown.addEventListener('click', function (event) {
        event.stopPropagation();
    });
});
</script> -->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notificationWrapper = document.getElementById('notificationWrapper');
        const notificationButton = document.getElementById('notificationButton');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationList = document.getElementById('notificationList');
        const notificationCount = document.getElementById('notificationCount');
        const markAllButton = document.getElementById('markAllNotificationsRead');
        const deleteReadButton = document.getElementById('deleteReadNotifications');

        if (!notificationWrapper || !notificationButton || !notificationDropdown || !notificationList) {
            return;
        }

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        const routes = {
            topbar: "{{ route('notifications.topbar') }}",
            readAll: "{{ route('notifications.read.all') }}",
            deleteRead: "{{ route('notifications.delete.read') }}",
            readBase: "{{ url('/notificaciones') }}",
        };

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getNotificationIcon(typeCode) {
            if (typeCode === 'OUT_OF_STOCK') return '⛔';
            if (typeCode === 'LOW_STOCK') return '📦';
            if (typeCode === 'SALE_CANCELLED') return '❌';
            if (typeCode === 'SALE_PENDING') return '⏳';
            if (typeCode === 'SALE_COMPLETED') return '✅';
            return '🔔';
        }

        function getSwalThemeOptions() {
            const isDark =
                document.body.dataset.theme === 'dark' ||
                document.documentElement.dataset.theme === 'dark';

            return {
                background: isDark ? '#0b1b36' : '#ffffff',
                color: isDark ? '#f8fafc' : '#0f172a'
            };
        }

        async function showSystemAlert(options) {
            if (typeof Swal === 'undefined') {
                console.warn('SweetAlert2 no está cargado.');
                return;
            }

            return Swal.fire({
                ...getSwalThemeOptions(),
                ...options
            });
        }

        async function loadNotifications() {
            try {
                const response = await fetch(routes.topbar, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    notificationList.innerHTML = `
                        <div class="notification-empty">
                            No se pudieron cargar las notificaciones.
                        </div>
                    `;
                    return;
                }

                const data = await response.json();

                const unreadCount = Number(data.unread_count || 0);
                const notifications = data.notifications || [];

                if (unreadCount > 0) {
                    notificationCount.textContent = unreadCount > 9 ? '9+' : unreadCount;
                    notificationCount.style.display = 'inline-flex';
                } else {
                    notificationCount.style.display = 'none';
                }

                if (notifications.length === 0) {
                    notificationList.innerHTML = `
                        <div class="notification-empty">
                            No tienes notificaciones.
                        </div>
                    `;
                    return;
                }

                notificationList.innerHTML = notifications.map(function (item) {
                    const unreadClass = item.is_read ? '' : 'unread';

                    return `
                        <div class="notification-item ${unreadClass}" data-id="${escapeHtml(item.id)}">
                            <button type="button" class="notification-item-main" data-id="${escapeHtml(item.id)}">
                                <span class="notification-item-icon">
                                    ${getNotificationIcon(item.type_code)}
                                </span>

                                <span class="notification-item-content">
                                    <strong>${escapeHtml(item.title)}</strong>
                                    <small>${escapeHtml(item.message)}</small>
                                    <em>${escapeHtml(item.created_at)}</em>
                                </span>
                            </button>

                            <button type="button" class="notification-delete-btn" data-id="${escapeHtml(item.id)}" title="Eliminar notificación">
                                ×
                            </button>
                        </div>
                    `;
                }).join('');

                document.querySelectorAll('.notification-item-main').forEach(function (itemButton) {
                    itemButton.addEventListener('click', async function () {
                        const notificationId = this.dataset.id;

                        await fetch(`${routes.readBase}/${notificationId}/leer`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        await loadNotifications();
                    });
                });

                document.querySelectorAll('.notification-delete-btn').forEach(function (deleteButton) {
                    deleteButton.addEventListener('click', async function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const notificationId = this.dataset.id;

                        const response = await fetch(`${routes.readBase}/${notificationId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) {
                            const errorData = await response.json().catch(() => ({}));
                            console.error('No se pudo eliminar la notificación:', errorData);
                            await showSystemAlert({
                                title: 'No se pudo eliminar',
                                text: 'Ocurrió un problema al eliminar la notificación.',
                                icon: 'error',
                                confirmButtonText: 'Entendido',
                                confirmButtonColor: '#3b82f6'
                            });
                            return;
                        }

                        await loadNotifications();
                    });
                });
            } catch (error) {
                notificationList.innerHTML = `
                    <div class="notification-empty">
                        Error al cargar notificaciones.
                    </div>
                `;
            }
        }

        notificationButton.addEventListener('click', function (event) {
            event.stopPropagation();

            notificationDropdown.classList.toggle('show');

            const branchDropdown = document.getElementById('branchDropdown');
            if (branchDropdown) {
                branchDropdown.classList.remove('show');
            }

            loadNotifications();
        });

        notificationDropdown.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        document.addEventListener('click', function () {
            notificationDropdown.classList.remove('show');
        });

        if (markAllButton) {
            markAllButton.addEventListener('click', async function (event) {
                event.preventDefault();
                event.stopPropagation();

                try {
                    const response = await fetch(routes.readAll, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        console.error('Error al marcar notificaciones como leídas');
                        return;
                    }

                    await loadNotifications();
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        }

       if (deleteReadButton) {
            deleteReadButton.addEventListener('click', async function (event) {
                event.preventDefault();
                event.stopPropagation();

                const confirmDelete = await showSystemAlert({
                    title: '¿Borrar notificaciones leídas?',
                    text: 'Esta acción eliminará todas las notificaciones que ya fueron marcadas como leídas.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, borrar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b'
                });

                if (!confirmDelete || !confirmDelete.isConfirmed) {
                    return;
                }

                const response = await fetch(routes.deleteRead, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    console.error('No se pudieron borrar las notificaciones leídas:', errorData);

                    await showSystemAlert({
                        title: 'No se pudieron borrar',
                        text: errorData.message || 'Ocurrió un problema al borrar las notificaciones leídas.',
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#3b82f6'
                    });

                    return;
                }

                await loadNotifications();

                await showSystemAlert({
                    title: 'Notificaciones eliminadas',
                    text: 'Las notificaciones leídas fueron eliminadas correctamente.',
                    icon: 'success',
                    timer: 1600,
                    showConfirmButton: false
                });
            });
        }

        loadNotifications();
        setInterval(loadNotifications, 30000);
    });
</script>


@endif