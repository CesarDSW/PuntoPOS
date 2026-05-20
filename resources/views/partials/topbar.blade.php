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
    <div class="topbar-left">
        <input type="text" placeholder="Buscar..." readonly>
    </div>

    <div class="topbar-right">
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
            }
        });
    }

    applyBranchModalMode();
});
</script>
@endif