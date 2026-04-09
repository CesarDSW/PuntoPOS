<header class="topbar">
    <div class="topbar-left">
        <input type="text" placeholder="Buscar..." readonly>
    </div>

    <div class="topbar-right">
        <div class="branch-selector">
            <button type="button" class="branch-button" id="branchButton">
                <span class="branch-label">Sucursal actual</span>
                <span class="branch-name" id="currentBranchName">Cargando...</span>
            </button>

            <div class="branch-dropdown" id="branchDropdown">
                <div id="branchDropdownList"></div>

                <div class="branch-dropdown-divider"></div>

                <button type="button" class="branch-create-link" id="openCreateBranchModal">
                    + Crear nueva sucursal
                </button>
            </div>
        </div>

        <div class="user-box">
            <div>
                <div style="font-weight: bold;">{{ auth()->user()->name_user }}</div>
                <div style="font-size: 12px; color: #64748b;">Administrador</div>
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

<div class="branch-modal-backdrop" id="branchModalBackdrop">
    <div class="branch-modal">
        <div class="branch-modal-header">
            <div class="branch-modal-icon">🏬</div>

            <div class="branch-modal-title-wrap">
                <h2 class="branch-modal-title">Crear nueva sucursal</h2>
                <p class="branch-modal-subtitle">Expande tu negocio a nuevas ubicaciones</p>
            </div>

            <button type="button" class="branch-modal-close" id="closeCreateBranchModal">×</button>
        </div>

        <form id="createBranchForm" class="branch-modal-body">
            <div class="branch-section">
                <div class="branch-section-title">Información básica</div>

                <label class="branch-field-label" for="name_branch">Nombre de la sucursal *</label>
                <input type="text" id="name_branch" name="name_branch" class="branch-field-input" placeholder="Ej: Sucursal Plaza Norte" maxlength="50" required>
            </div>

            <div class="branch-section">
                <div class="branch-section-title">Ubicación</div>

                <label class="branch-field-label" for="address">Dirección *</label>
                <input type="text" id="address" name="address" class="branch-field-input" placeholder="Calle, número, colonia" maxlength="50" required>

                <label class="branch-field-label" for="city_state">Ciudad y Estado *</label>
                <input type="text" id="city_state" name="city_state" class="branch-field-input" placeholder="Ej: Ciudad de México, CDMX" maxlength="101" required>

                <label class="branch-field-label" for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone" class="branch-field-input" placeholder="(55) 1234-5678" maxlength="10">
            </div>

            <div class="branch-section">
                <div class="branch-section-title">Responsable de la sucursal</div>

                <label class="branch-field-label" for="responsible">Nombre del responsable</label>
                <input type="text" id="responsible" name="responsible" class="branch-field-input" placeholder="Nombre completo" maxlength="50">

                <label class="branch-field-label" for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" class="branch-field-input" placeholder="correo@ejemplo.com" maxlength="320">
            </div>

            <div class="branch-note">
                <strong>Nota:</strong> Una vez creada la sucursal, podrás asignar usuarios, configurar inventario independiente y gestionar las ventas de forma separada.
            </div>

            <div id="branchFormMessage" class="branch-form-message" style="display: none;"></div>

            <div class="branch-modal-footer">
                <button type="button" class="branch-cancel-btn" id="cancelCreateBranchModal">Cancelar</button>
                <button type="submit" class="branch-submit-btn" id="submitCreateBranch">
                    <span id="submitCreateBranchText">Crear sucursal</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .branch-dropdown-divider {
        height: 1px;
        background: #e5e7eb;
        margin: 8px 0;
    }

    .branch-create-link {
        width: 100%;
        border: none;
        background: transparent;
        text-align: left;
        padding: 12px 14px;
        border-radius: 10px;
        cursor: pointer;
        color: #1d4ed8;
        font-weight: 600;
    }

    .branch-create-link:hover {
        background: #eff6ff;
    }

    .branch-empty {
        padding: 12px 14px;
        color: #64748b;
    }

    .branch-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        z-index: 9999;
    }

    .branch-modal-backdrop.show {
        display: flex;
    }

    .branch-modal {
        width: 100%;
        max-width: 760px;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
        background: #ffffff;
        border-radius: 18px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.18);
    }

    .branch-modal-header {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 22px 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .branch-modal-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #eef2ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .branch-modal-title-wrap {
        flex: 1;
    }

    .branch-modal-title {
        font-size: 20px;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .branch-modal-subtitle {
        color: #64748b;
        font-size: 14px;
    }

    .branch-modal-close {
        border: none;
        background: transparent;
        color: #64748b;
        font-size: 30px;
        cursor: pointer;
        line-height: 1;
        flex-shrink: 0;
    }

    .branch-modal-body {
        padding: 22px 24px 24px;
    }

    .branch-section {
        margin-bottom: 22px;
    }

    .branch-section-title {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 14px;
    }

    .branch-field-label {
        display: block;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 8px;
        margin-top: 14px;
    }

    .branch-field-input {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 14px 16px;
        font-size: 16px;
        outline: none;
    }

    .branch-field-input:focus {
        border-color: #1d4ed8;
        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
    }

    .branch-note {
        background: #f5f7ff;
        border: 1px solid #c7d2fe;
        color: #0f172a;
        border-radius: 12px;
        padding: 16px;
        line-height: 1.5;
        margin-top: 8px;
    }

    .branch-form-message {
        margin-top: 18px;
        padding: 12px 14px;
        border-radius: 12px;
        font-size: 14px;
    }

    .branch-form-message.error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }

    .branch-form-message.success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
    }

    .branch-modal-footer {
        display: flex;
        gap: 14px;
        justify-content: space-between;
        padding-top: 22px;
        border-top: 1px solid #e5e7eb;
        margin-top: 24px;
    }

    .branch-cancel-btn,
    .branch-submit-btn {
        flex: 1;
        padding: 14px 18px;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
    }

    .branch-cancel-btn {
        border: 1px solid #d1d5db;
        background: #ffffff;
        color: #0f172a;
    }

    .branch-submit-btn {
        border: 1px solid #1d4ed8;
        background: #1d4ed8;
        color: #ffffff;
    }

    .branch-submit-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', async function () {
    const branchButton = document.getElementById('branchButton');
    const branchDropdown = document.getElementById('branchDropdown');
    const branchDropdownList = document.getElementById('branchDropdownList');
    const currentBranchName = document.getElementById('currentBranchName');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const openCreateBranchModal = document.getElementById('openCreateBranchModal');
    const closeCreateBranchModal = document.getElementById('closeCreateBranchModal');
    const cancelCreateBranchModal = document.getElementById('cancelCreateBranchModal');
    const branchModalBackdrop = document.getElementById('branchModalBackdrop');
    const createBranchForm = document.getElementById('createBranchForm');
    const branchFormMessage = document.getElementById('branchFormMessage');
    const submitCreateBranch = document.getElementById('submitCreateBranch');
    const submitCreateBranchText = document.getElementById('submitCreateBranchText');

    async function loadBranches() {
        const response = await fetch('/api/branches', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json().catch(() => ({}));

        currentBranchName.textContent = data.current_branch_name ?? 'Sin sucursal';
        branchDropdownList.innerHTML = '';

        if (!data.branches || data.branches.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'branch-empty';
            empty.textContent = 'No hay sucursales registradas';
            branchDropdownList.appendChild(empty);
            return;
        }

        data.branches.forEach(branch => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'branch-option' + (branch.branch_id == data.current_branch_id ? ' active' : '');
            btn.textContent = branch.name_branch;

            btn.addEventListener('click', async () => {
                const saveResponse = await fetch('/api/branches/current', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        branch_id: branch.branch_id
                    })
                });

                if (saveResponse.ok) {
                    window.location.reload();
                }
            });

            branchDropdownList.appendChild(btn);
        });
    }

    function openModal() {
        branchModalBackdrop.classList.add('show');
        branchDropdown.classList.remove('show');
        branchFormMessage.style.display = 'none';
        branchFormMessage.textContent = '';
        branchFormMessage.className = 'branch-form-message';
    }

    function closeModal() {
        branchModalBackdrop.classList.remove('show');
        createBranchForm.reset();
        branchFormMessage.style.display = 'none';
        branchFormMessage.textContent = '';
        branchFormMessage.className = 'branch-form-message';
    }

    function showError(message) {
        branchFormMessage.textContent = message;
        branchFormMessage.className = 'branch-form-message error';
        branchFormMessage.style.display = 'block';
    }

    function showSuccess(message) {
        branchFormMessage.textContent = message;
        branchFormMessage.className = 'branch-form-message success';
        branchFormMessage.style.display = 'block';
    }

    branchButton.addEventListener('click', function () {
        branchDropdown.classList.toggle('show');
    });

    document.addEventListener('click', function (e) {
        if (!branchButton.contains(e.target) && !branchDropdown.contains(e.target)) {
            branchDropdown.classList.remove('show');
        }
    });

    openCreateBranchModal.addEventListener('click', function () {
        openModal();
    });

    closeCreateBranchModal.addEventListener('click', function () {
        closeModal();
    });

    cancelCreateBranchModal.addEventListener('click', function () {
        closeModal();
    });

    branchModalBackdrop.addEventListener('click', function (e) {
        if (e.target === branchModalBackdrop) {
            closeModal();
        }
    });

    createBranchForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        branchFormMessage.style.display = 'none';
        branchFormMessage.textContent = '';
        branchFormMessage.className = 'branch-form-message';

        const formData = new FormData(createBranchForm);
        const cityState = (formData.get('city_state') || '').trim();

        if (!cityState.includes(',')) {
            showError('En "Ciudad y Estado" escribe el formato: Ciudad, Estado');
            return;
        }

        const cityStateParts = cityState.split(',');
        const state = cityStateParts.pop().trim();
        const city = cityStateParts.join(',').trim();

        if (city === '' || state === '') {
            showError('En "Ciudad y Estado" escribe el formato: Ciudad, Estado');
            return;
        }

        const payload = {
            name_branch: (formData.get('name_branch') || '').trim(),
            address: (formData.get('address') || '').trim(),
            city: city,
            state: state,
            phone: (formData.get('phone') || '').trim(),
            responsible: (formData.get('responsible') || '').trim(),
            email: (formData.get('email') || '').trim(),
        };

        submitCreateBranch.disabled = true;
        submitCreateBranchText.textContent = 'Creando...';

        try {
            const response = await fetch('/api/branches', {
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

                submitCreateBranch.disabled = false;
                submitCreateBranchText.textContent = 'Crear sucursal';
                return;
            }

            showSuccess(data.message || 'Sucursal creada correctamente.');

            setTimeout(() => {
                window.location.reload();
            }, 700);
        } catch (error) {
            showError('Ocurrió un error al crear la sucursal.');
            submitCreateBranch.disabled = false;
            submitCreateBranchText.textContent = 'Crear sucursal';
        }
    });

    loadBranches();
});
</script>