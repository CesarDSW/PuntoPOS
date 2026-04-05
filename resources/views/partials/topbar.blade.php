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

            <div class="branch-dropdown" id="branchDropdown"></div>
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

<script>
document.addEventListener('DOMContentLoaded', async function () {
    const branchButton = document.getElementById('branchButton');
    const branchDropdown = document.getElementById('branchDropdown');
    const currentBranchName = document.getElementById('currentBranchName');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    async function loadBranches() {
        const response = await fetch('/api/branches', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json().catch(() => ({}));

        currentBranchName.textContent = data.current_branch_name ?? 'Sin sucursal';
        branchDropdown.innerHTML = '';

        if (!data.branches || data.branches.length === 0) {
            const empty = document.createElement('div');
            empty.style.padding = '12px 14px';
            empty.textContent = 'No hay sucursales registradas';
            branchDropdown.appendChild(empty);
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

            branchDropdown.appendChild(btn);
        });
    }

    branchButton.addEventListener('click', function () {
        branchDropdown.classList.toggle('show');
    });

    document.addEventListener('click', function (e) {
        if (!branchButton.contains(e.target) && !branchDropdown.contains(e.target)) {
            branchDropdown.classList.remove('show');
        }
    });

    loadBranches();
});
</script>