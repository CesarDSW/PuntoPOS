@extends('layout.dashboard_design')

@section('content')

<style>

.support-page{
    padding:30px;
}

.support-top{
    margin-bottom:25px;
}

.support-top h1{
    font-size:36px;
    font-weight:700;
    color:#111827;
    margin-bottom:8px;
}

.support-top p{
    color:#6b7280;
    font-size:15px;
}

/* STATS */

.support-stats{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
    margin-bottom:25px;
}

.stat-card{
    background:#fff;
    padding:20px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.05);
}

.stat-card h3{
    font-size:32px;
    color:#2563eb;
    margin:0;
}

.stat-card p{
    color:#64748b;
    margin-top:5px;
}

/* SEARCH */

.search-box{
    margin-bottom:20px;
}

.search-box input{
    width:100%;
    padding:12px 15px;
    border:1px solid #e5e7eb;
    border-radius:12px;
    outline:none;
}

/* TABLE */

.support-table{
    background:#fff;
    border-radius:20px;
    padding:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.05);
}

.table-scroll{
    max-height:600px;
    overflow-y:auto;
}

.support-table table{
    width:100%;
    border-collapse:collapse;
}

.support-table thead{
    background:#f8fafc;
}

.support-table th{
    padding:16px;
    text-align:left;
    font-size:14px;
    color:#475569;
    font-weight:600;
}

.support-table td{
    padding:16px;
    border-bottom:1px solid #f1f5f9;
    color:#111827;
    font-size:14px;
}

.support-table tbody tr:hover{
    background:#f8fafc;
}

/* USER */

.user-ticket{
    display:flex;
    align-items:center;
    gap:10px;
}

.avatar{
    width:40px;
    height:40px;
    border-radius:50%;
    background:#2563eb;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
}

/* STATUS */

.status{
    background:#fef3c7;
    color:#92400e;
    padding:8px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
}

.done{
    background:#dcfce7;
    color:#166534;
    padding:8px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
}

/* BUTTON */

.btn-completar{
    background:#10b981;
    color:white;
    border:none;
    padding:10px 14px;
    border-radius:10px;
    cursor:pointer;
    font-size:13px;
    font-weight:600;
    transition:.2s;
}

.btn-completar:hover{
    background:#059669;
}

/* DROPDOWN */

.dropdown{
position:relative;
}

.dropdown-btn{
border:none;
background:none;
font-size:22px;
cursor:pointer;
color:#64748b;
font-weight:bold;
}

.dropdown-menu{
position:absolute;
right:0;
top:35px;
background:#fff;
min-width:180px;
border-radius:12px;
box-shadow:0 10px 25px rgba(0,0,0,.12);
display:none;
z-index:100;
}

.dropdown.active .dropdown-menu{
display:block;
}


.dropdown-menu a,
.dropdown-menu button{
width:100%;
display:block;
text-align:left;
padding:12px 15px;
border:none;
background:none;
cursor:pointer;
text-decoration:none;
color:#111827;
font-size:14px;
}

.dropdown-menu a:hover,
.dropdown-menu button:hover

.table-scroll{
    max-height:600px;
    overflow-y:auto;
}

.support-table{
    overflow:visible;
}

.table-scroll{
    overflow-x:auto;
    overflow-y:visible;
}

.custom-pagination{

    display:flex;

    justify-content:center;

    align-items:center;

    gap:8px;

    margin-top:25px;

}

.page-btn{

    min-width:40px;

    height:40px;

    display:flex;

    align-items:center;

    justify-content:center;

    background:#fff;

    border:1px solid #e5e7eb;

    border-radius:10px;

    text-decoration:none;

    color:#111827;

    font-weight:600;

}

.page-btn:hover{

    background:#2563eb;

    color:white;

}

.page-btn.active{

    background:#2563eb;

    color:white;

    border-color:#2563eb;

}

.page-btn.disabled{

    opacity:.4;

    pointer-events:none;

}

/* RESPONSIVE */

@media(max-width:768px){

    .support-page{
        padding:15px;
    }

    .support-top h1{
        font-size:28px;
    }

    .support-stats{
        grid-template-columns:1fr;
    }

}

</style>

<div class="support-page">


<div class="support-top">

    <h1>🎫 Tickets de soporte</h1>

    <p>
        Administra las dudas y problemas enviados por los usuarios
    </p>

</div>

<div class="support-stats">

    <div class="stat-card">
        <h3>{{ $totalTickets }}</h3>
        <p>Total tickets</p>
    </div>

    <div class="stat-card">
        <h3>{{ $totalPendientes }}</h3>
        <p>Pendientes</p>
    </div>

    <div class="stat-card">
        <h3>{{ $totalAtendidos }}</h3>
        <p>Atendidos</p>
    </div>

</div>

<div class="support-table">

<form method="GET" class="filters">

    <input
        type="text"
        name="search"
        placeholder="🔍 Buscar asunto o mensaje..."
        value="{{ request('search') }}">

    <select name="status">

        <option value="">
            Todos los estados
        </option>

        <option
            value="pendiente"
            {{ request('status') == 'pendiente' ? 'selected' : '' }}>

            Pendientes

        </option>

        <option
            value="atendido"
            {{ request('status') == 'atendido' ? 'selected' : '' }}>

            Atendidos

        </option>

    </select>

    <button type="submit">

        Buscar

    </button>

</form>



    <div class="table-scroll">

        <table>

            <thead>

                <tr>

                    <th>Usuario</th>
                    <th>Sucursal</th>
                    <th>Asunto</th>
                    <th>Mensaje</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acción</th>

                </tr>

            </thead>

            <tbody>
                


@forelse($tickets as $ticket)

<tr>

    <td>

        <div class="user-ticket">

            <div class="avatar">

                {{ strtoupper(substr($ticket->user->name_user ?? 'U',0,1)) }}

            </div>

            <div>

                {{ $ticket->user->name_user ?? 'Sin usuario' }}

            </div>

        </div>

    </td>

    <td>

        {{ $ticket->branch->name_branch ?? 'Sin sucursal' }}

    </td>

    <td>

        {{ $ticket->subject }}

    </td>

    <td>

        {{ \Illuminate\Support\Str::limit($ticket->message,50) }}

    </td>

    <td>

        @if($ticket->status == 'pendiente')

            <span class="status">

                Pendiente

            </span>

        @else

            <span class="done">

                Atendido

            </span>

        @endif

    </td>

    <td>

        {{ $ticket->created_at->format('d/m/Y') }}

    </td>




    <td style="text-align:center;">

        <div class="dropdown">

            <button
    type="button"
    class="dropdown-btn"
    onclick="toggleDropdown(this)">

    ⋮

</button>

            <div class="dropdown-menu">

                <a href="mailto:{{ $ticket->user->email ?? '#' }}">

                    ✉ Enviar mensaje

                </a>

                @if($ticket->status == 'pendiente')

                    <form
                        method="POST"
                        action="{{ route('support.completar', $ticket->id) }}">

                        @csrf

                        <button type="submit">

                            ✔ Marcar atendido

                        </button>

                    </form>

                @else

                    <button
                        type="button"
                        disabled>

                        ✔ Ya atendido

                    </button>

                @endif

            </div>

        </div>

    </td>

</tr>

@empty

<tr>

    <td colspan="7">

        No hay tickets registrados

    </td>

</tr>

@endforelse




            </tbody>

        </table>
       @if ($tickets->hasPages())

<div class="custom-pagination">

    @if ($tickets->onFirstPage())

        <span class="page-btn disabled">←</span>

    @else

        <a
            href="{{ $tickets->previousPageUrl() }}"
            class="page-btn">

            ←

        </a>

    @endif

    @for ($i = 1; $i <= $tickets->lastPage(); $i++)

        <a
            href="{{ $tickets->url($i) }}"
            class="page-btn {{ $tickets->currentPage() == $i ? 'active' : '' }}">

            {{ $i }}

        </a>

    @endfor

    @if ($tickets->hasMorePages())

        <a
            href="{{ $tickets->nextPageUrl() }}"
            class="page-btn">

            →

        </a>

    @else

        <span class="page-btn disabled">→</span>

    @endif

</div>

@endif
</div>

    </div>

</div>
</div>

<script>

function toggleDropdown(button)
{
    let dropdown = button.closest('.dropdown');

    document.querySelectorAll('.dropdown').forEach(item => {

        if(item !== dropdown)
        {
            item.classList.remove('active');
        }

    });

    dropdown.classList.toggle('active');
}

document.addEventListener('click', function(e){

    if(!e.target.closest('.dropdown'))
    {
        document.querySelectorAll('.dropdown').forEach(item => {

            item.classList.remove('active');

        });
    }

});

</script>


@endsection
