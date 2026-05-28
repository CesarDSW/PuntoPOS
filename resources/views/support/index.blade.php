@extends('layout.dashboard_design')

@section('content')

<style>

/* =========================
   SUPPORT PAGE
========================= */

.support-page{

    padding:30px;

}

/* HEADER */

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

/* CARD */

.support-table{

    background:#fff;

    border-radius:20px;

    padding:20px;

    box-shadow:0 10px 30px rgba(0,0,0,.05);

    overflow-x:auto;

}

/* TABLE */

.support-table table{

    width:100%;

    border-collapse:collapse;

}

/* HEAD */

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

/* BODY */

.support-table td{

    padding:16px;

    border-bottom:1px solid #f1f5f9;

    color:#111827;

    font-size:14px;

}

/* HOVER */

.support-table tbody tr:hover{

    background:#f8fafc;

}

/* STATUS */

.status{

    background:#dbeafe;

    color:#2563eb;

    padding:6px 12px;

    border-radius:999px;

    font-size:12px;

    font-weight:600;

    display:inline-block;

}

/* BTN */

.btn-completar{

    background:#2563eb;

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

    background:#1d4ed8;

}

/* DONE */

.done{

    color:#16a34a;

    font-weight:700;

}

/* RESPONSIVE */

@media(max-width:768px){

    .support-page{

        padding:15px;

    }

    .support-top h1{

        font-size:28px;

    }

    .support-table{

        padding:12px;

    }

}

</style>

<div class="support-page">

    <!-- HEADER -->
    <div class="support-top">

        <h1>
            Tickets de soporte
        </h1>

        <p>
            Administra las dudas y problemas enviados por los usuarios
        </p>

    </div>

    <!-- TABLE -->
    <div class="support-table">

        <table>

            <thead>

                <tr>

                    <th>Usuario</th>

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
                            {{ $ticket->user_id }}
                        </td>

                        <td>
                            {{ $ticket->subject }}
                        </td>

                        <td>
                            {{ $ticket->message }}
                        </td>

                        <td>

                            <span class="status">

                                {{ $ticket->status }}

                            </span>

                        </td>

                        <td>
                            {{ $ticket->created_at->format('d/m/Y') }}
                        </td>

                        <td>

                            @if($ticket->status == 'pendiente')

                                <form
                                    method="POST"
                                    action="{{ route('support.completar', $ticket->id) }}">

                                    @csrf

                                    <button class="btn-completar">

                                        Marcar atendido

                                    </button>

                                </form>

                            @else

                                <span class="done">

                                    ✔ Atendido

                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6">

                            No hay tickets registrados

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection