@extends('layout.dashboard_design')

@section('content')
<div class="settings-page">
    <div class="settings-header">
        <h1>Configuración</h1>
        <p>Administra las preferencias de tu negocio.</p>
    </div>

    @if(session('success'))
        <div class="success-box">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any() && request('tab') !== 'seguridad')
        <div class="error-box">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="settings-layout">
        <aside class="settings-menu">
            <a
                href="{{ route('settings', ['tab' => 'perfil']) }}"
                class="settings-menu-item {{ request('tab', 'perfil') == 'perfil' ? 'active' : '' }}"
            >
                🏢 Perfil del negocio
            </a>

            <a
                href="{{ route('settings', ['tab' => 'usuarios']) }}"
                class="settings-menu-item {{ request('tab') == 'usuarios' ? 'active' : '' }}"
            >
                👥 Usuarios y roles
            </a>

            <a
                href="{{ route('settings', ['tab' => 'pagos']) }}"
                class="settings-menu-item {{ request('tab') == 'pagos' ? 'active' : '' }}"
            >
                💳 Métodos de pago
            </a>

            <a
                href="{{ route('settings', ['tab' => 'notificaciones']) }}"
                class="settings-menu-item {{ request('tab') == 'notificaciones' ? 'active' : '' }}"
            >
                🔔 Notificaciones
            </a>

            <a
                href="{{ route('settings', ['tab' => 'seguridad']) }}"
                class="settings-menu-item {{ request('tab') == 'seguridad' ? 'active' : '' }}"
            >
                🛡️ Seguridad
            </a>

            <a
                href="{{ route('settings', ['tab' => 'preferencias']) }}"
                class="settings-menu-item {{ request('tab') == 'preferencias' ? 'active' : '' }}"
            >
                ⚙️ Preferencias
            </a>
        </aside>

        <section class="settings-content">
            @if(request('tab', 'perfil') == 'perfil')
                @if($canEditBusinessProfile)
                    <div class="settings-card">
                        <h2>🏢 Información del negocio</h2>

                        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="tab_section" value="perfil">

                            <div class="form-group">
                                <label>Logo</label>
                                <input type="file" name="logo" class="form-input">
                            </div>

                            @if(!empty($company->logo))
                                <div class="form-group">
                                    <label>Logo actual</label><br>
                                    <img
                                        src="{{ asset('storage/' . $company->logo) }}"
                                        alt="Logo de la empresa"
                                        style="max-width: 180px; border-radius: 12px; border: 1px solid #d1d5db; padding: 8px;"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                                    >
                                </div>
                                <div style="display:none;">Logo no disponible</div>
                            @endif

                            <div class="form-group">
                                <label>Nombre de la empresa</label>
                                <input
                                    type="text"
                                    name="name_company"
                                    class="form-input"
                                    value="{{ old('name_company', $company->name_company) }}"
                                >
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>RFC</label>
                                    <input
                                        type="text"
                                        name="rfc"
                                        class="form-input"
                                        value="{{ old('rfc', $company->rfc) }}"
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Correo</label>
                                    <input
                                        type="email"
                                        name="email"
                                        class="form-input"
                                        value="{{ old('email', $company->email) }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Dirección</label>
                                <input
                                    type="text"
                                    name="address"
                                    class="form-input"
                                    value="{{ old('address', $company->address) }}"
                                >
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Ciudad</label>
                                    <input
                                        type="text"
                                        name="city"
                                        class="form-input"
                                        value="{{ old('city', $company->city) }}"
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Estado</label>
                                    <input
                                        type="text"
                                        name="state"
                                        class="form-input"
                                        value="{{ old('state', $company->state) }}"
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Código postal</label>
                                    <input
                                        type="text"
                                        name="zip_code"
                                        class="form-input"
                                        value="{{ old('zip_code', $company->zip_code) }}"
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <input
                                        type="text"
                                        name="phone"
                                        class="form-input"
                                        value="{{ old('phone', $company->phone) }}"
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Hora de apertura</label>
                                    <input
                                        type="time"
                                        name="opening_time"
                                        class="form-input"
                                        value="{{ old('opening_time', $company->opening_time) }}"
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Hora de cierre</label>
                                    <input
                                        type="time"
                                        name="closing_time"
                                        class="form-input"
                                        value="{{ old('closing_time', $company->closing_time) }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Tipo de moneda</label>
                                <select name="currency" class="form-input">
                                    <option value="">Selecciona</option>
                                    <option value="MXN" @selected(old('currency', $company->currency) == 'MXN')>MXN - Peso Mexicano</option>
                                    <option value="USD" @selected(old('currency', $company->currency) == 'USD')>USD - Dólar</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Descripción del negocio</label>
                                <textarea name="description_company" class="form-textarea">{{ old('description_company', $company->description_company) }}</textarea>
                            </div>

                            <button type="submit" class="btn-save">Guardar cambios</button>
                        </form>

                        <div class="settings-card" style="margin-top: 18px;">
                            <h2>🏬 Sucursales registradas</h2>
                            <p>Consulta las sucursales creadas y su responsable actual.</p>

                            <div class="users-list">
                                @forelse($branchCards as $branchCard)
                                    <div class="user-card">
                                        <div>
                                            <h3>{{ $branchCard->name_branch }}</h3>
                                            <p>{{ $branchCard->address }}, {{ $branchCard->city }}, {{ $branchCard->state }}</p>
                                            <p style="margin-top; 6px;">Telélefono: {{ $branchCard->phone ?: 'No asignado' }}</p>
                                            <p>Responsable: {{ $branchCard->responsible ?: 'No asignado' }}</p>
                                            <p>Correo: {{ $branchCard->email ?: 'No asignado' }}</p>
                                        </div>

                                        <div class="user-actions-right">
                                            <span class="role-badge">{{ $branchCard->name_branch }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <p>No hay sucursales registradas.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @else
                    <div class="settings-card">
                        <h2>🏢 Perfil del negocio</h2>
                        <p>Como gerente puedes consultar la información de la empresa y tu sucursal asignada, pero no modificarla.</p>

                        <div class="form-group">
                            <label>Empresa / Sucursal</label>
                            <input
                                type="text"
                                class="form-input"
                                value="{{ $company->name_company }}{{ $assignedBranch ? ' - ' . $assignedBranch->name_branch : '' }}"
                                readonly
                            >
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>RFC de la empresa</label>
                                <input type="text" class="form-input" value="{{ $company->rfc }}" readonly>
                            </div>

                            <div class="form-group">
                                <label>Correo de la sucursal</label>
                                <input type="text" class="form-input" value="{{ optional($assignedBranch)->email ?? 'No asignado' }}" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Responsable de la sucursal</label>
                                <input type="text" class="form-input" value="{{ optional($assignedBranch)->responsible ?? 'No asignado' }}" readonly>
                            </div>

                            <div class="form-group">
                                <label>Teléfono de la sucursal</label>
                                <input type="text" class="form-input" value="{{ optional($assignedBranch)->phone ?? 'No asignado' }}" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Dirección de la sucursal</label>
                            <input type="text" class="form-input" value="{{ optional($assignedBranch)->address ?? 'No asignada' }}" readonly>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Ciudad</label>
                                <input type="text" class="form-input" value="{{ optional($assignedBranch)->city ?? 'No asignada' }}" readonly>
                            </div>

                            <div class="form-group">
                                <label>Estado</label>
                                <input type="text" class="form-input" value="{{ optional($assignedBranch)->state ?? 'No asignado' }}" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Moneda del negocio</label>
                            <input type="text" class="form-input" value="{{ $company->currency ?: 'No confirmada' }}" readonly>
                        </div>
                    </div>
                @endif

            @elseif(request('tab') == 'usuarios')
                <div class="settings-card">
                    <div class="users-header">
                        <div>
                            <h2>👥 Usuarios y roles</h2>
                            <p>Gestiona el acceso al sistema</p>
                        </div>

                        @if($access['can_create_users'])
                            <button type="button" class="btn-new-user" onclick="openUserModal()">
                                + Nuevo usuario
                            </button>
                        @endif
                    </div>

                    <div class="roles-info-grid">
                        <div class="role-info-card role-admin">
                            <h3>Administrador</h3>
                            <p>Control total del sistema, configuración y acceso completo a todas las funciones.</p>
                        </div>

                        <div class="role-info-card role-manager">
                            <h3>Gerente</h3>
                            <p>Gestión de inventario, usuarios, clientes y reportes operativos.</p>
                        </div>

                        <div class="role-info-card role-cashier">
                            <h3>Cajero</h3>
                            <p>Realiza ventas, consulta productos y atiende clientes en el POS.</p>
                        </div>
                    </div>

                    <div class="users-list">
                        @forelse($users as $userItem)
                            <div class="user-card">
                                <div>
                                    <h3>{{ $userItem->name_user }}</h3>
                                    <p>{{ $userItem->email }}</p>

                                    @if(!empty($userItem->branch_idfk))
                                        @php
                                            $userAssignedBranch = $branches->firstWhere('branch_id', $userItem->branch_idfk);
                                        @endphp

                                        @if($userAssignedBranch)
                                            <p style="margin-top: 6px;">Sucursal: {{ $userAssignedBranch->name_branch }}</p>
                                        @endif
                                    @endif
                                </div>

                                <div class="user-actions-right">
                                    @php
                                        $targetRoleModel = $roles->firstWhere('rol_id', $userItem->rol_idfk);
                                        $targetRoleName = strtoupper(trim((string) optional($targetRoleModel)->type_rol));

                                        $displayRole = match($targetRoleName) {
                                            'ADMIN', 'ADMINISTRADOR' => 'Administrador',
                                            'GERENTE' => 'Gerente',
                                            'CAJERO' => 'Cajero',
                                            default => $targetRoleModel->type_rol ?? 'Sin rol',
                                        };

                                        $canEdit = (bool) ($userItem->can_edit_ui ?? false);
                                        $canDelete = (bool) ($userItem->can_delete_ui ?? false);
                                    @endphp

                                    <span class="role-badge">{{ $displayRole }}</span>

                                    <div class="user-action-buttons">
                                        @if($canEdit)
                                            <button
                                                type="button"
                                                class="btn-icon-edit"
                                                onclick="openEditUserModal(
                                                    '{{ $userItem->userr_id }}',
                                                    @js($userItem->name_user),
                                                    @js($userItem->phone),
                                                    @js($userItem->email),
                                                    '{{ $userItem->rol_idfk }}',
                                                    '{{ $userItem->branch_idfk ?? '' }}',
                                                    @js($userItem->permission_states ?? []) 
                                                )"
                                            >
                                                Editar
                                            </button>
                                        @endif

                                        @if($canDelete)
                                            <form method="POST" action="{{ route('users.delete', $userItem->userr_id) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-icon-delete">Eliminar</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p>No hay usuarios registrados.</p>
                        @endforelse
                    </div>
                </div>

                <div class="modal-overlay" id="userModal">
                    <div class="modal-box">
                        <div class="modal-header">
                            <h2>Nuevo usuario</h2>
                            <button type="button" class="modal-close" onclick="closeUserModal()">×</button>
                        </div>

                        <form method="POST" action="{{ route('users.store') }}">
                            @csrf

                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Nombre del usuario</label>
                                    <input type="text" name="name_user" class="form-input" value="{{ old('name_user') }}" required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <input type="text" name="phone" class="form-input" value="{{ old('phone') }}" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Rol</label>
                                    <select name="rol_idfk" id="create_rol_idfk" class="form-input" required>
                                        <option value="">Selecciona un rol</option>

                                        @foreach($roles as $rol)
                                            @php
                                                $roleOptionName = strtoupper(trim((string) $rol->type_rol));
                                                $normalizedRoleOption = \App\Support\UserAccess::normalizeRoleName($roleOptionName);

                                                $canShowRole = match($normalizedRoleOption) {
                                                    'ADMINISTRADOR' => $access['can_create_admin'],
                                                    'GERENTE' => $access['can_create_manager'],
                                                    'CAJERO' => $access['can_create_cashier'],
                                                    default => false,
                                                };
                                            @endphp

                                            @if($canShowRole)
                                                <option value="{{ $rol->rol_id }}" @selected((string) old('rol_idfk') === (string) $rol->rol_id)>
                                                    {{ $rol->type_rol }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group" id="createBranchGroup" style="display: none;">
                                    <label for="create_branch_idfk">Sucursal (opcional)</label>
                                    <select name="branch_idfk" id="create_branch_idfk" class="form-input">
                                        <option value="">Asignar después</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->branch_id }}" @selected((string) old('branch_idfk') === (string) $branch->branch_id)>
                                                {{ $branch->name_branch }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <small id="createBranchHelp" class="text-muted" style="display:block; margin-top:6px;">
                                        Puedes crear al usuario primero y asignarle sucursal después desde Editar usuario.
                                    </small>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Contraseña</label>
                                        <input type="password" name="password" class="form-input" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Confirmar contraseña</label>
                                        <input type="password" name="password_confirmation" class="form-input" required>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn-secondary" onclick="closeUserModal()">Cancelar</button>
                                <button type="submit" class="btn-save">Guardar usuario</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal-overlay" id="editUserModal">
                    <div class="modal-box">
                        <div class="modal-header">
                            <h2>Editar usuario</h2>
                            <button type="button" class="modal-close" onclick="closeEditUserModal()">×</button>
                        </div>

                        <form method="POST" id="editUserForm">
                            @csrf
                            @method('PUT')

                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Nombre del usuario</label>
                                    <input type="text" name="name_user" id="edit_name_user" class="form-input" required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" id="edit_email" class="form-input" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <input type="text" name="phone" id="edit_phone" class="form-input" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Rol</label>
                                    <input type="hidden" name="rol_idfk" id="edit_rol_idfk_hidden">

                                    <select id="edit_rol_idfk" class="form-input">
                                        <option value="">Selecciona un rol</option>
                                        
                                        @foreach($roles as $rol)
                                            @php
                                                $normalizedRoleOption = \App\Support\UserAccess::normalizeRoleName($rol->type_rol);

                                                $canShowRole = match($normalizedRoleOption) {
                                                    'ADMINISTRADOR' => $access['can_create_admin'] || $access['can_edit_admin'],
                                                    'GERENTE' => $access['can_create_manager'],
                                                    'CAJERO' => $access['can_create_cashier'],
                                                    default => false,
                                                };
                                            @endphp

                                            @if($canShowRole)
                                                <option value="{{ $rol->rol_id }}">{{ $rol->type_rol }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group" id="editBranchGroup" style="display: none;">
                                    <label for="edit_branch_idfk">Sucursal (opcional)</label>

                                    <input type="hidden" name="branch_idfk" id="edit_branch_idfk_hidden">

                                    <select id="edit_branch_idfk" class="form-input">
                                        <option value="">Sin sucursal asignada</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->branch_id }}">{{ $branch->name_branch }}</option>
                                        @endforeach
                                    </select>

                                    <small class="text-muted" style="display:block; margin-top:6px;">
                                        Desde aquí puedes cambiar o quitar la sucursal asignada al usuario.
                                    </small>
                                </div>

                                @if($access['can_manage_permissions'] && $grantablePermissions->count())
                                    <div class="form-group">
                                        <label>Permisos específicos</label>

                                        <div class="form-row">
                                            @foreach ($grantablePermissions as $permission)
                                                <div class="form-group">
                                                    <label>{{ $permission->description_permission }}</label>
                                                    <select 
                                                        name="permission_states[{{ $permission->code_permission }}]" 
                                                        class="form-input permission-state"
                                                        data-code="{{ $permission->code_permission }}"
                                                    >
                                                        <option value="inherit">Heredar del rol</option>
                                                        <option value="allow">Permitir</option>
                                                        <option value="deny">Bloquear</option>
                                                    </select>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn-secondary" onclick="closeEditUserModal()">Cancelar</button>
                                <button type="submit" class="btn-save">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    const currentUserId = {{ (int) $currentUserId }};
                    const currentRoleName = @js($currentRoleName);
                    const ownerUserId = {{ (int) $ownerUserId }};
                    const branchesCount = {{ (int) $branches->count() }};

                    function openUserModal() {
                        document.getElementById('userModal').style.display = 'flex';
                        toggleCreateBranchField();
                    }

                    function closeUserModal() {
                        document.getElementById('userModal').style.display = 'none';
                    }

                    function openEditUserModal(id, name, phone, email, rolId, branchId, permissionStates = {}) {
                        const isSelf = Number(currentUserId) === Number(id);
                        const isTargetOwner = Number(ownerUserId) === Number(id);

                        document.getElementById('edit_name_user').value = name;
                        document.getElementById('edit_phone').value = phone;
                        document.getElementById('edit_email').value = email;
                        document.getElementById('editUserForm').action = '/configuracion/usuarios/' + id;

                        const roleSelect = document.getElementById('edit_rol_idfk');
                        const roleHidden = document.getElementById('edit_rol_idfk_hidden');
                        const branchSelect = document.getElementById('edit_branch_idfk');
                        const branchHidden = document.getElementById('edit_branch_idfk_hidden');

                        if (roleSelect) roleSelect.value = rolId ?? '';
                        if (roleHidden) roleHidden.value = rolId ?? '';

                        if (branchSelect) branchSelect.value = branchId ?? '';
                        if (branchHidden) branchHidden.value = branchId ?? '';

                        document.querySelectorAll('.permission-state').forEach(function (select) {
                            const code = select.dataset.code;
                            const state = permissionStates?.[code] || 'inherit';
                            select.value = state;
                        });

                        if ((isSelf && currentRoleName === 'GERENTE') || isTargetOwner) {
                            if (roleSelect) roleSelect.disabled = true;
                        } else {
                            if (roleSelect) roleSelect.disabled = false;
                        }

                        toggleEditBranchField();
                        document.getElementById('editUserModal').style.display = 'flex';
                    }

                    function closeEditUserModal() {
                        document.getElementById('editUserModal').style.display = 'none';
                    }

                    function roleTextFromSelect(selectId) {
                        const select = document.getElementById(selectId);
                        if (!select) return '';

                        return select.options[select.selectedIndex]?.text?.trim()?.toUpperCase() || '';
                    }

                    function toggleCreateBranchField() {
                        const selectedText = roleTextFromSelect('create_rol_idfk');
                        const branchGroup = document.getElementById('createBranchGroup');
                        const branchHelp = document.getElementById('createBranchHelp');

                        if (!branchGroup) return;

                        const shouldShow = selectedText === 'CAJERO' || selectedText === 'GERENTE';
                        branchGroup.style.display = shouldShow ? 'block' : 'none';

                        if (!shouldShow) {
                            const select = document.getElementById('create_branch_idfk');
                            if (select) select.value = '';
                            return;
                        }

                        if (branchHelp) {
                            if (branchesCount === 0) {
                                branchHelp.textContent = 'Todavía no hay sucursales registradas. Puedes crear el usuario y asignarle una después.';
                            } else {
                                branchHelp.textContent = 'La sucursal es opcional al crear. También puedes asignarla después desde Editar usuario.';
                            }
                        }
                    }

                    function toggleEditBranchField() {
                        const selectedText = roleTextFromSelect('edit_rol_idfk');
                        const branchGroup = document.getElementById('editBranchGroup');
                        const branchHidden = document.getElementById('edit_branch_idfk_hidden');

                        if (!branchGroup) return;

                        const shouldShow = selectedText === 'CAJERO' || selectedText === 'GERENTE';
                        branchGroup.style.display = shouldShow ? 'block' : 'none';

                        if (!shouldShow && branchHidden) {
                            branchHidden.value = '';
                        }
                    }

                    document.addEventListener('DOMContentLoaded', function () {
                        const createRole = document.getElementById('create_rol_idfk');
                        const editRole = document.getElementById('edit_rol_idfk');
                        const editRoleHidden = document.getElementById('edit_rol_idfk_hidden');
                        const editBranch = document.getElementById('edit_branch_idfk');
                        const editBranchHidden = document.getElementById('edit_branch_idfk_hidden');

                        if (createRole) {
                            createRole.addEventListener('change', toggleCreateBranchField);
                            toggleCreateBranchField();
                        }

                        if (editRole) {
                            editRole.addEventListener('change', function () {
                                if (editRoleHidden) {
                                    editRoleHidden.value = editRole.value;
                                }
                                toggleEditBranchField();
                            });
                        }

                        if (editBranch) {
                            editBranch.addEventListener('change', function () {
                                if (editBranchHidden) {
                                    editBranchHidden.value = editBranch.value;
                                }
                            });
                        }

                        toggleEditBranchField();
                    });
                </script>

            @elseif(request('tab') == 'pagos')
                <div class="settings-card">
                    <h2>💳 Métodos de pago</h2>
                    <p>Selecciona los métodos de pago disponibles para tu negocio.</p>

                    @php
                        $paymentMethods = $company->payment_methods
                            ? json_decode($company->payment_methods, true)
                            : [];
                    @endphp

                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        <input type="hidden" name="tab_section" value="pagos">

                        <div class="form-group">
                            <label>Selecciona un método de pago</label>

                            <div class="payment-grid">
                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Efectivo" {{ in_array('Efectivo', $paymentMethods) ? 'checked' : '' }}>
                                    <span>💵 Efectivo</span>
                                </label>

                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Tarjeta" {{ in_array('Tarjeta', $paymentMethods) ? 'checked' : '' }}>
                                    <span>💳 Tarjeta</span>
                                </label>

                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Transferencia" {{ in_array('Transferencia', $paymentMethods) ? 'checked' : '' }}>
                                    <span>🔁 Transferencia</span>
                                </label>

                                <label class="payment-option">
                                    <input type="checkbox" name="payment_methods[]" value="Cheque" {{ in_array('Cheque', $paymentMethods) ? 'checked' : '' }}>
                                    <span>🧾 Cheque</span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn-save">Guardar cambios</button>
                    </form>
                </div>

            @elseif(request('tab') == 'notificaciones')
                <div class="settings-card">
                    <h2>🔔 Notificaciones</h2>
                    <p>Configura las notificaciones que deseas recibir sobre tu negocio.</p>

                    <form method="POST" action="{{ route('settings.notifications.update') }}">
                        @csrf

                        <div class="settings-option-card">
                            <div>
                                <h3>Stock bajo</h3>
                                <p>Recibir alerta cuando un producto tenga poco inventario.</p>
                            </div>

                            <label class="switch">
                                <input type="checkbox" name="notify_low_stock" {{ $settings->notify_low_stock ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="settings-option-card">
                            <div>
                                <h3>Venta cancelada</h3>
                                <p>Recibir alerta cuando una venta sea cancelada.</p>
                            </div>

                            <label class="switch">
                                <input type="checkbox" name="notify_sale_cancelled" {{ $settings->notify_sale_cancelled ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="settings-option-card">
                            <div>
                                <h3>Producto agotado</h3>
                                <p>Recibir alerta cuando un producto llegue a 0 en stock.</p>
                            </div>

                            <label class="switch">
                                <input type="checkbox" name="notify_out_of_stock" {{ $settings->notify_out_of_stock ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <button type="submit" class="btn-save">Guardar notificaciones</button>
                    </form>
                </div>

            @elseif(request('tab') == 'seguridad')
                <div class="settings-card">
                    <h2>🛡️ Seguridad</h2>
                    <p>Administra la seguridad de tu cuenta y protege el acceso a tu negocio.</p>

                    @if(session('success_password'))
                        <div class="success-box" style="margin-bottom: 16px;">
                            {{ session('success_password') }}
                        </div>
                    @endif

                    @if(session('status') == 'two-factor-authentication-enabled')
                        <div class="success-box" style="margin-bottom: 16px;">
                            Escanea el código QR y confirma el código de Google Authenticator.
                        </div>
                    @endif

                    @if(session('status') == 'two-factor-authentication-confirmed')
                        <div class="success-box" style="margin-bottom: 16px;">
                            La autenticación en dos pasos fue confirmada correctamente.
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="error-box" style="margin-bottom: 16px;">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="security-block">
                        <div class="security-block-header">
                            <h3>Cambiar contraseña</h3>
                            <p>Actualiza tu contraseña periódicamente.</p>
                        </div>

                        <form method="POST" action="{{ route('settings.password.update') }}">
                            @csrf

                            <div class="form-group">
                                <label>Contraseña actual</label>
                                <input type="password" name="current_password" class="form-input" required>
                            </div>

                            <div class="form-group">
                                <label>Nueva contraseña</label>
                                <input type="password" name="new_password" class="form-input" required>
                            </div>

                            <div class="form-group">
                                <label>Confirmar contraseña</label>
                                <input type="password" name="new_password_confirmation" class="form-input" required>
                            </div>

                            <div class="security-info-box">
                                <strong>Requisitos:</strong> La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números.
                            </div>

                            <button type="submit" class="btn-save">Cambiar contraseña</button>
                        </form>
                    </div>

                    <div class="security-block">
                        <div class="security-block-header twofa-header">
                            <div class="twofa-header-icon">📱</div>
                            <div>
                                <h3>Autenticación de dos factores (2FA)</h3>
                                <p>Agrega una capa extra de seguridad a tu cuenta.</p>
                            </div>
                        </div>

                        @if(empty(auth()->user()->two_factor_secret))
                            <div class="twofa-status-box twofa-status-off">
                                <div>
                                    <strong>2FA desactivado</strong>
                                    <p>Activa la autenticación de dos factores para mayor seguridad.</p>
                                </div>

                                <form method="POST" action="/user/two-factor-authentication">
                                    @csrf
                                    <button type="submit" class="btn-save">Activar verificación en dos pasos</button>
                                </form>
                            </div>
                        @else
                            <div class="twofa-status-box twofa-status-on">
                                <div>
                                    <strong>2FA en proceso o activado</strong>
                                    <p>Escanea el código QR con Google Authenticator y confirma el código.</p>
                                </div>
                            </div>

                            <div class="twofa-qr-box">
                                {!! auth()->user()->twoFactorQrCodeSvg() !!}
                            </div>

                            @if(is_null(auth()->user()->two_factor_confirmed_at))
                                <form method="POST" action="/user/confirmed-two-factor-authentication" class="twofa-confirm-form">
                                    @csrf

                                    <div class="form-group">
                                        <label>Código de verificación</label>
                                        <input type="text" name="code" class="form-input" maxlength="6" placeholder="123456" required>
                                    </div>

                                    <button type="submit" class="btn-save">Confirmar verificación</button>
                                </form>
                            @else
                                <div class="twofa-confirmed-badge">
                                    Verificación en dos pasos activada correctamente.
                                </div>
                            @endif

                            <div class="recovery-section">
                                <h4>Códigos de recuperación</h4>
                                <p>Guárdalos en un lugar seguro. Te servirán si pierdes acceso a tu teléfono.</p>

                                <div class="recovery-codes-box">
                                    @foreach(auth()->user()->recoveryCodes() as $code)
                                        <div>{{ $code }}</div>
                                    @endforeach
                                </div>

                                <form method="POST" action="/user/two-factor-recovery-codes" style="margin-top: 14px;">
                                    @csrf
                                    <button type="submit" class="btn-secondary">Regenerar códigos</button>
                                </form>
                            </div>

                            <form method="POST" action="/user/two-factor-authentication" style="margin-top: 18px;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger">Desactivar verificación en dos pasos</button>
                            </form>
                        @endif
                    </div>
                </div>

            @elseif(request('tab') == 'preferencias')
                @php
                    $timezoneOptions = \App\Support\TimezoneCatalog::options();
                    $currentTimezone = old('timezone', $settings->timezone ?? 'America/Mexico_City');
                @endphp
                
                <div class="settings-card">
                    <h2>⚙️ Preferencias</h2>
                    <p>Configura tus preferencias generales del sistema.</p>

                    <form method="POST" action="{{ route('settings.preferences.update') }}">
                        @csrf

                        <div class="inner-card">
                            <h3>Configuración regional</h3>
                            <p>Zona horaria y formatos</p>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="timezone">Zona horaria</label>

                                    <input
                                        type="text"
                                        name="timezone"
                                        id="timezone"
                                        list="timezoneList"
                                        class="form-input"
                                        value="{{ $currentTimezone }}"
                                        placeholder="Ej. America/Mexico_City"
                                        autocomplete="off"
                                        required
                                    >

                                    <datalist id="timezoneList">
                                        @foreach($timezoneOptions as $option)
                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                        @endforeach    
                                    </datalist>

                                    <small style="display:block; margin-top:8px; opacity:.8; font-size:12px;">
                                        Puedes escribir y buscar zonas como
                                        <strong>America/Mexico_City</strong>,
                                        <strong>America/Tijuana</strong>,
                                        <strong>Europe/Madrid</strong> o
                                        <strong>Asia/Tokyo</strong>.
                                    </small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Formato de fecha</label>
                                    <select name="date_format" class="form-input">
                                        <option value="d/m/Y" @selected($settings->date_format == 'd/m/Y')>d/m/Y (31/12/2024)</option>
                                        <option value="m/d/Y" @selected($settings->date_format == 'm/d/Y')>m/d/Y (12/31/2024)</option>
                                        <option value="Y-m-d" @selected($settings->date_format == 'Y-m-d')>Y-m-d (2024-12-31)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Formato de hora</label>
                                    <select name="time_format" class="form-input">
                                        <option value="H:i" @selected($settings->time_format == 'H:i')>24 horas (18:30)</option>
                                        <option value="h:i A" @selected($settings->time_format == 'h:i A')>12 horas (06:30 PM)</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Decimales en precios</label>
                                    <select name="price_decimals" class="form-input">
                                        <option value="2" @selected((string) $settings->price_decimals === '2')>2 decimales ($100.00)</option>
                                        <option value="0" @selected((string) $settings->price_decimals === '0')>0 decimales ($100)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="inner-card">
                            <h3>Preferencias de impresión</h3>
                            <p>Configuración de tickets y recibos</p>

                            <div class="settings-option-card">
                                <div>
                                    <h3>Impresión automática</h3>
                                    <p>Imprimir ticket automáticamente al completar una venta.</p>
                                </div>

                                <label class="switch">
                                    <input type="checkbox" name="auto_print" {{ $settings->auto_print ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="settings-option-card">
                                <div>
                                    <h3>Mostrar impuestos</h3>
                                    <p>Desglosar impuestos en tickets y recibos.</p>
                                </div>

                                <label class="switch">
                                    <input type="checkbox" name="show_taxes" {{ $settings->show_taxes ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="form-group">
                                <label>Tamaño de impresora térmica</label>

                                <div class="printer-options">
                                    <label class="printer-option {{ $settings->printer_width == '80mm' ? 'selected' : '' }}">
                                        <input type="radio" name="printer_width" value="80mm" {{ $settings->printer_width == '80mm' ? 'checked' : '' }}>
                                        <span>80mm</span>
                                        <small>Impresora estándar</small>
                                    </label>

                                    <label class="printer-option {{ $settings->printer_width == '58mm' ? 'selected' : '' }}">
                                        <input type="radio" name="printer_width" value="58mm" {{ $settings->printer_width == '58mm' ? 'checked' : '' }}>
                                        <span>58mm</span>
                                        <small>Impresora compacta</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="inner-card">
                            <h3>Preferencias de visualización</h3>
                            <p>Apariencia y formato de números</p>

                            <div class="form-group">
                                <label>Tema de la interfaz</label>

                                <div class="theme-options">
                                    <label class="theme-card {{ in_array($settings->theme, ['light', 'Claro'], true) ? 'selected' : '' }}">
                                        <input type="radio" name="theme" value="light" {{ in_array($settings->theme, ['light', 'Claro'], true) ? 'checked' : '' }}>
                                        <div class="theme-content">
                                            <span>☀️ Claro</span>
                                        </div>
                                    </label>

                                    <label class="theme-card {{ in_array($settings->theme, ['dark', 'Oscuro'], true) ? 'selected' : '' }}">
                                        <input type="radio" name="theme" value="dark" {{ in_array($settings->theme, ['dark', 'Oscuro'], true) ? 'checked' : '' }}>
                                        <div class="theme-content">
                                            <span>🌙 Oscuro</span>
                                        </div>
                                    </label>

                                    <label class="theme-card {{ in_array($settings->theme, ['auto', 'Auto'], true) ? 'selected' : '' }}">
                                        <input type="radio" name="theme" value="auto" {{ in_array($settings->theme, ['auto', 'Auto'], true) ? 'checked' : '' }}>
                                        <div class="theme-content">
                                            <span>⚙️ Auto</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        @php 
                            $clientedigitalCompanyId = (int) ($company->company_id ?? 0);
                            $clientedigitalBranchId = (int) (optional($assignedBranch)->branch_id ?? 0);
                            $clientedigitalUserId = (int) ($currentUserId ?? 0);
                        @endphp

                        <div class="inner-card" style="margin-top: 18px;" id="clientedigitalIntegrationCard">
                            <h3>Integración con ClienteDigital</h3>
                            <p>Conecta Punto con ClienteDigital para importar productos y ventas.</p>

                            <div class="form-row" style="margin-top: 14px;">
                                <div class="form-group">
                                    <label>Código de vinculación</label>
                                    <input 
                                        type="text"
                                        id="clientedigitalIntegrationCode"
                                        class="form-input"
                                        placeholder="Ejemplo: CD-PUNTO-ABC123"
                                        autocomplete="off"
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Estado</label>
                                    <input 
                                        type="text"
                                        id="clientedigitalIntegrationStatus"
                                        class="form-input"
                                        value="No conectado"
                                        readonly
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>URL de ClienteDigital</label>
                                    <input 
                                        type="text"
                                        id="clientedigitalBaseUrl"
                                        class="form-input"
                                        value="http://localhost/clientedigital/index.php/apis"
                                    >
                                </div>
                            </div>

                            <div class="preferences-actions" style="margin-top: 12px; display: flex; gap: 10px; flex-wrap: wrap;">
                                <button
                                    type="button"
                                    class="btn-save"
                                    id="btnConnectClienteDigital"
                                >
                                    Canjear código
                                </button>

                                <button
                                    type="button"
                                    class="btn-secondary"
                                    id="btnSyncClienteDigitalProducts"
                                    disabled
                                >
                                    Sincronizar productos
                                </button>
                                
                                <button
                                    type="button"
                                    class="btn-secondary"
                                    id="btnSyncClienteDigitalSales"
                                    disabled
                                >
                                    Sincronizar ventas
                                </button>
                            </div>

                            <div id="clientedigitalIntegrationMessage" style="margin-top: 12px;"></div>

                            @if(!$clientedigitalBranchId)
                                <div class="error-box" style="margin-top: 12px;">
                                    Para importar productos con stock y ventas, este usuario debe tener una sucursal asignada.
                                </div>
                            @endif
                        </div>

                        <div class="preferences-actions">
                            <button type="submit" class="btn-save">Guardar preferencias</button>
                        </div>

                        <div class="inner-card" style="margin-top: 18px;">
                            <h3>Resumen actual</h3>
                            <p>Configuración activa del sistema.</p>

                            <div class="form-row" style="margin-top: 14px;">
                                <div class="form-group">
                                    <label>Tamaño de ticket</label>
                                    <input type="text" class="form-input" value="{{ $settings->printer_width }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Impresión automática</label>
                                    <input type="text" class="form-input" value="{{ $settings->auto_print ? 'Activada' : 'Desactivada' }}" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Mostrar impuestos</label>
                                    <input type="text" class="form-input" value="{{ $settings->show_taxes ? 'Sí' : 'No' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Decimales</label>
                                    <input type="text" class="form-input" value="{{ $settings->price_decimals }}" readonly>
                                </div>
                            </div>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('settings.preferences.reset') }}" style="margin-top: 12px;">
                        @csrf
                        <button type="submit" class="btn-secondary">Restablecer valores por defecto</button>
                    </form>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        function syncSelectableCards(inputName, cardSelector) {
                            const cards = document.querySelectorAll(cardSelector);

                            cards.forEach((card) => {
                                const input = card.querySelector(`input[name="${inputName}"]`);
                                if (!input) return;

                                if (input.checked) {
                                    card.classList.add('selected');
                                } else {
                                    card.classList.remove('selected');
                                }

                                card.addEventListener('click', function () {
                                    input.checked = true;

                                    cards.forEach((otherCard) => {
                                        otherCard.classList.remove('selected');
                                    });

                                    card.classList.add('selected');
                                });

                                input.addEventListener('change', function () {
                                    cards.forEach((otherCard) => {
                                        otherCard.classList.remove('selected');
                                    });

                                    if (input.checked) {
                                        card.classList.add('selected');
                                    }
                                });
                            });
                        }

                        syncSelectableCards('printer_width', '.printer-option');
                        syncSelectableCards('theme', '.theme-card');

                        const hiddenInput = document.getElementById('timezone');
                        const textInput = document.getElementById('timezone_search');
                        const dropdown = document.getElementById('timezone_dropdown');
                        const toggle = document.getElementById('timezone_toggle');
                        const options = Array.from(document.querySelectorAll('.timezone-option'));

                        if (!hiddenInput || !textInput || !dropdown || !toggle || !options.length) {
                            return;
                        }

                        function openDropdown() {
                            dropdown.classList.add('show');
                        }

                        function closeDropdown() {
                            dropdown.classList.remove('show');
                        }

                        function selectOption(button) {
                            const value = button.dataset.value || '';
                            const label = button.dataset.label || value;

                            hiddenInput.value = value;
                            textInput.value = label;

                            options.forEach((options) => option.classList.remove('selected'));
                            button.classList.add('selected');

                            closeDropdown();
                        }

                        function filterOptions() {
                            const search = textInput.value.trim().toLowerCase();

                            let visibleCount = 0;

                            options.forEach((option) => {
                                const value = (option.dataset.value || '').toLowerCase();
                                const label = (option.dataset.label || '').toLowerCase();
                                const matches = search === '' value.includes(search) || label.includes(search);

                                options.style.display = matches ? 'block' : 'none';

                                if (matches) {
                                    visibleCount++;
                                }
                            });

                            if (visibleCount > 0) {
                                openDropdown();
                            } else {
                                closeDropdown();
                            }
                        }

                        function restoreSelectedLabel() {
                            const selected = options.find((option) => option.dataset.value === hiddenInput.value);

                            if (selected) {
                                textInput.value = selected.dataset.label || selected.dataset.value || '';
                                option.forEach((option) => option.classList.remove('selected'));
                                selected.classList.add('selected');
                            }
                        }

                        toggle.addEventListener('click', function () {
                            if (dropdwon.classList.contains('show')) {
                                closeDropdown();
                            } else {
                                filterOptions();
                                openDropdown();
                                textInput.focus();
                            }
                        });

                        textInput.addEventListener('focus', function () {
                            filterOptions();
                            openDropdown();
                        });

                        textInput.addEventListener('input', filterOptions);

                        textInput.addEventListener('blur', function () {
                            setTimeout(() => {
                               const typed = textInput.value.trim().toLowerCase();
                               
                               const exactMatch = options.find((option) => {
                                    const value = (option.dataset.value || '').toLowerCase();
                                    const label = (option.dataset.label || '').toLowerCase();

                                    return value === typed || label === typed;
                                });

                                if (exactMatch) {
                                    selectOption(exactMatch);
                                } else {
                                    restoreSelectedLabel();
                                    closeDropdown();
                                }
                            }, 150);
                        });

                        document.addEventListener('click', function (event) {
                            const inside = event.target.closest('.timezone-field');
                            if (!inside) {
                                closeDropdown();
                            }
                        });

                        options.forEach((options) => {
                            options.addEventListener('click', function  () {
                                selectOption(option);
                            });
                        });

                        restoreSelectedLabel();

                        const clienteDigitalConfig = {
                            companyId: {{ $clientedigitalCompanyId }},
                            branchId: {{ $clientedigitalBranchId }},
                            userId: {{ $clientedigitalUserId }},
                            connectUrl: "{{ url('/api/integrations/clientedigital/connect') }}",
                            listUrl: "{{ url('/api/integrations/clientedigital') }}"
                        };

                        let clienteDigitalIntegrationId = null;

                        const cdCodeInput = document.getElementById('clientedigitalIntegrationCode');
                        const cdBaseUrlInput = document.getElementById('clientedigitalBaseUrl');
                        const cdStatusInput = document.getElementById('clientedigitalIntegrationStatus');
                        const cdMessageBox = document.getElementById('clientedigitalIntegrationMessage');
                        const cdConnectButton = document.getElementById('btnConnectClienteDigital');
                        const cdSyncProductsButton = document.getElementById('btnSyncClienteDigitalProducts');
                        const cdSyncSalesButton = document.getElementById('btnSyncClienteDigitalSales');

                        function showClienteDigitalMessage(type, message) {
                            if (!cdMessageBox) return;

                            const background = type === 'success' ? '#dcfce7' : '#fee2e2';
                            const color = type === 'success' ? '#166534' : '#991b1b';
                            const border = type === 'success' ? '#86efac' : '#fecaca';

                            cdMessageBox.innerHTML = `
                                <div style="background: ${background}; color: ${color}; border: 1px solid ${border}; padding: 12px; border-radius: 12px;">
                                    ${message}
                                </div>
                            `;
                        }

                        function setClienteDigitalConnected(integrationId) {
                            clienteDigitalIntegrationId = integrationId;

                            if (cdStatusInput) {
                                cdStatusInput.value = integrationId
                                    ? 'Conectado'
                                    : 'No conectado';
                            }

                            if (cdSyncProductsButton) {
                                cdSyncProductsButton.disabled = !integrationId;
                            }

                            if (cdSyncSalesButton) {
                                cdSyncSalesButton.disabled = !integrationId;
                            }
                        }

                        async function loadClienteDigitalIntegration() {
                            if (!cdStatusInput) return;

                            try {
                                const response = await fetch(clienteDigitalConfig.listUrl, {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                });

                                const result = await response.json();

                                if (!response.ok || !result.success) {
                                    return;
                                }

                                const integrations = result.data || [];

                                const activeIntegration = integrations.find((integration) => {
                                    return integration.source_app === 'clientedigital'
                                        && integration.status === 'active'
                                        && Number(integration.company_idfk) === Number(clienteDigitalConfig.companyId);
                                });

                                if (activeIntegration) {
                                    setClienteDigitalConnected(activeIntegration.id);

                                    if (cdBaseUrlInput && activeIntegration.external_base_url) {
                                        cdBaseUrlInput.value = activeIntegration.external_base_url;
                                    }
                                }
                            } catch (error) {
                                console.warn('No se pudo cargar la integración con ClienteDigital.', error);
                            }
                        }

                        async function connectClienteDigital() {
                            if (!clienteDigitalConfig.companyId || !clienteDigitalConfig.userId) {
                                showClienteDigitalMessage('error', 'No se pudo identificar la empresa o el usuario actual.');
                                return;
                            }

                            if (!clienteDigitalConfig.branchId) {
                                showClienteDigitalMessage('error', 'Debes tener una sucursal asignada antes de conectar ClienteDigital.');
                                return;
                            }

                            const code = cdCodeInput ? cdCodeInput.value.trim() : '';
                            const baseUrl = cdBaseUrlInput ? cdBaseUrlInput.value.trim() : '';

                            if (!code) {
                                showClienteDigitalMessage('error', 'Escribe el código generado en ClienteDigital.');
                                return;
                            }

                            if (!baseUrl) {
                                showClienteDigitalMessage('error', 'La URL de ClienteDigital es obligatoria.');
                                return;
                            }

                            cdConnectButton.disabled = true;
                            cdConnectButton.textContent = 'Conectando...';

                            try {
                                const response = await fetch(clienteDigitalConfig.connectUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        external_base_url: baseUrl,
                                        integration_code: code,
                                        company_idfk: clienteDigitalConfig.companyId,
                                        branch_idfk: clienteDigitalConfig.branchId,
                                        userr_idfk: clienteDigitalConfig.userId
                                    })
                                });

                                const result = await response.json();

                                if (!response.ok || !result.success) {
                                    throw new Error(result.message || 'No se pudo conectar con ClienteDigital.');
                                }

                                const integrationId = result.data.integration.id;

                                setClienteDigitalConnected(integrationId);

                                showClienteDigitalMessage('success', 'Integración conectada correctamente.');
                            } catch (error) {
                                showClienteDigitalMessage('error', error.message);
                            } finally {
                                cdConnectButton.disabled = false;
                                cdConnectButton.textContent = 'Canjear código';
                            }
                        }

                        async function syncClienteDigital(type) {
                            if (!clienteDigitalIntegrationId) {
                                showClienteDigitalMessage('error', 'Primero conecta ClienteDigital.');
                                return;
                            }

                            const isProducts = type === 'products';
                            const button = isProducts ? cdSyncProductsButton : cdSyncSalesButton;
                            const endpoint = isProducts ? 'sync-products' : 'sync-sales';

                            button.disabled = true;
                            button.textContent = isProducts ? 'Sincronizando productos...' : 'Sincronizando ventas...';

                            try {
                                const response = await fetch(`/api/integrations/clientedigital/${clienteDigitalIntegrationId}/${endpoint}`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        limit: 100,
                                        offset: 0
                                    })
                                });

                                const result = await response.json();

                                if (!response.ok || !result.success) {
                                    throw new Error(result.message || 'No se pudo sincronizar.');
                                }

                                const summary = result.data.summary;

                                showClienteDigitalMessage(
                                    'success',
                                    `Sincronización finalizada. Creados: ${summary.created}, actualizados: ${summary.updated}, omitidos: ${summary.skipped}, fallidos: ${summary.failed}.`
                                );
                            } catch (error) {
                                showClienteDigitalMessage('error', error.message);
                            } finally {
                                button.disabled = false;
                                button.textContent = isProducts ? 'Sincronizar productos' : 'Sincronizar ventas';
                            }
                        }

                        if (cdConnectButton) {
                            cdConnectButton.addEventListener('click', connectClienteDigital);
                        }

                        if (cdSyncProductsButton) {
                            cdSyncProductsButton.addEventListener('click', function () {
                                syncClienteDigital('products');
                            });
                        }

                        if (cdSyncSalesButton) {
                            cdSyncSalesButton.addEventListener('click', function () {
                                syncClienteDigital('sales');
                            });
                        }

                        loadClienteDigitalIntegration();
                    });
                </script>
            @endif
        </section>
    </div>
</div>
@endsection