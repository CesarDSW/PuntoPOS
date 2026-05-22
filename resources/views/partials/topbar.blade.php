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
            <div class="user-info">
                <div class="user-name">{{ strtoupper(substr(optional(auth()->user())->name_user ?? 'U', 0, 1)) }}</div>
                <div class="user-role">Administrador</div>
            </div>

            <div class="avatar">
                {{ strtoupper(substr(optional(auth()->user())->name_user ?? 'U', 0, 1)) }}
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn">Cerrar sesión</button>
            </form>
        </div>
    </div>
</header>

<div id="branchModalOverlay" class="branch-modal-overlay">
    <div class="branch-modal">
        <div class="branch-modal-header">
            <div class="branch-modal-icon">🏬</div>

            <div class="branch-modal-title-wrap">
                <h2 class="branch-modal-title">Crear nueva sucursal</h2>
                <p class="branch-modal-subtitle">Expande tu negocio a nuevas ubicaciones</p>
            </div>

            <button type="button" class="branch-modal-close" id="closeCreateBranchModal">×</button>
        </div>

        <div class="branch-modal-body">
            <form id="createBranchForm">
                <div class="branch-section">
                    <div class="branch-section-title">Información básica</div>

                    <label class="branch-field-label" for="name_branch">Nombre de la sucursal *</label>
                    <input 
                        type="text" 
                        id="name_branch" 
                        name="name_branch" 
                        class="branch-field-input" 
                        placeholder="Ej: Sucursal Plaza Norte" 
                        maxlength="50" 
                        required
                    >
                </div>

                <div class="branch-section">
                    <div class="branch-section-title">Ubicación</div>

                    <label class="branch-field-label" for="address">Dirección *</label>
                    <input 
                        type="text" 
                        id="address" 
                        name="address" 
                        class="branch-field-input" 
                        placeholder="Calle, número, colonia" 
                        maxlength="50" 
                        required
                    >

                    <label class="branch-field-label" for="city_state">Ciudad y Estado *</label>
                    <input 
                        type="text" 
                        id="city_state" 
                        name="city_state" 
                        class="branch-field-input" 
                        placeholder="Ej: Ciudad de México, CDMX" 
                        maxlength="101" 
                        required
                    >

                    <label class="branch-field-label" for="phone">Teléfono</label>
                    <input 
                        type="text" 
                        id="phone" 
                        name="phone" 
                        class="branch-field-input" 
                        placeholder="(55) 1234-5678" 
                        maxlength="10"
                    >
                </div>

                <div class="branch-section">
                    <div class="branch-section-title">Responsable de la sucursal</div>

                    <label class="branch-field-label" for="responsible">Nombre del responsable</label>
                    <input 
                        type="text" 
                        id="responsible" 
                        name="responsible" 
                        class="branch-field-input" 
                        placeholder="Nombre completo" 
                        maxlength="50"
                    >

                    <label class="branch-field-label" for="email">Correo electrónico</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="branch-field-input" 
                        placeholder="correo@ejemplo.com" 
                        maxlength="320"
                    >
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
</div>

<script>
document.addEventListener('DOMContentLoaded', async function () {
    const branchButton = document.getElementById('branchButton');
    const branchDropdown = document.getElementById('branchDropdown');
    const branchDropdownList = document.getElementById('branchDropdownList');
    const currentBranchName = document.getElementById('currentBranchName');
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    const openCreateBranchModal = document.getElementById('openCreateBranchModal');
    const closeCreateBranchModal = document.getElementById('closeCreateBranchModal');
    const cancelCreateBranchModal = document.getElementById('cancelCreateBranchModal');
    
    const branchModalOverlay = document.getElementById('branchModalOverlay');
    
    const createBranchForm = document.getElementById('createBranchForm');
    const branchFormMessage = document.getElementById('branchFormMessage');
    const submitCreateBranch = document.getElementById('submitCreateBranch');
    const submitCreateBranchText = document.getElementById('submitCreateBranchText');

    async function loadBranches() {
        try {
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
        } catch (error){
            currentBranchName.textContent = 'Sin sucursal';
        }   
    }

    function resetBranchMessage() {
        branchFormMessage.style.display = 'none';
        branchFormMessage.textContent = '';
        branchFormMessage.className = 'branch-form-message';
    }

    function openModal() {
        if (!branchModalOverlay) return;

        branchModalOverlay.classList.add('show');
        document.body.classList.add('modal-open');

        if (branchDropdown){
            branchDropdown.classList.remove('show');
        }

        resetBranchMessage();
    }

    function closeModal() {
        if (!branchModalOverlay) return;

        branchModalOverlay.classList.remove('show');
        document.body.classList.remove('modal-open');

        if (createBranchForm){
            createBranchForm.reset();
        }

        resetBranchMessage();
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

    if (branchButton && branchDropdown) {
        branchButton.addEventListener('click', function (e) {
           e.stopPropagation();
           branchDropdown.classList.toggle('show');
        });
    }

    document.addEventListener('click', function (e) {
        if (
            branchButton && 
            branchDropdown &&
            !branchButton.contains(e.target) && 
            !branchDropdown.contains(e.target)
        ) {
            branchDropdown.classList.remove('show');
        }
    });

    if(openCreateBranchModal) {
        openCreateBranchModal.addEventListener('click', function () {
            openModal();
        });
    }

    if(closeCreateBranchModal) {
        closeCreateBranchModal.addEventListener('click', function () {
            closeModal();
        });
    }

    if(branchModalOverlay) {
        branchModalOverlay.addEventListener('click', function (e) {
            if (e.target === branchModalOverlay) {
                closeModal();
            }
        });
    }

    if(createBranchForm) {
        createBranchForm.addEventListener('submit', async function (e) {
            e.preventDefault();

           resetBranchMessage();

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
                const response = await fetch('/api/branches/store', {
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
    }

    loadBranches();
});
</script>