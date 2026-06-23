@extends('layout.dashboard_design')

@section('title', 'Soporte desarrollador')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/tickets/support.css') }}">
@endpush

@section('content')

<div class="support-page">
    <div class="support-hero">
        <div>
            <span class="support-eyebrow">Panel DEV</span>
            <h1>Soporte desarrollador</h1>
            <p>Administra los tickets enviados por los usuarios, revisa el estado de cada caso y responde desde una sola vista.</p>
        </div>
    </div>

    <div class="support-stats">
        <div class="support-stat-card">
            <div class="support-stat-icon">🎫</div>
            <div>
                <span>Total</span>
                <strong>{{ $totalTickets }}</strong>
            </div>
        </div>

        <div class="support-stat-card support-stat-alert">
            <div class="support-stat-icon">❗</div>
            <div>
                <span>Mensajes nuevos</span>
                <strong>{{ $unreadTickets }}</strong>
            </div>
        </div>

        <div class="support-stat-card">
            <div class="support-stat-icon">🟦</div>
            <div>
                <span>Abiertos</span>
                <strong>{{ $openTickets }}</strong>
            </div>
        </div>

        <div class="support-stat-card">
            <div class="support-stat-icon">🟢</div>
            <div>
                <span>Respondidos</span>
                <strong>{{ $answeredTickets }}</strong>
            </div>
        </div>

        <div class="support-stat-card">
            <div class="support-stat-icon">🔴</div>
            <div>
                <span>Cerrados</span>
                <strong>{{ $closedTickets }}</strong>
            </div>
        </div>
    </div>

    <div class="support-card">
        <div class="support-card-header">
            <div>
                <h2>Lista de tickets</h2>
                <p>Selecciona un ticket para ver la conversación y responder.</p>
            </div>
        </div>

        <div class="support-table">
            <div class="support-row support-row-head">
                <div>Asunto</div>
                <div>Usuario</div>
                <div>Sucursal</div>
                <div>Estado</div>
                <div>Nuevos</div>
                <div>Fecha</div>
                <div>Acción</div>
            </div>

            @forelse($tickets as $ticket)
                <div class="support-row {{ $ticket->unread_messages_count ?? 0 ? 'suppor-row-unread' : '' }}">
                    <div class="support-ticket-main">
                        <div class="support-ticket-title">
                            @if(($ticket->unread_messages_count ?? 0) > 0)
                                <span class="support-alert-dot">!</span>
                            @endif

                            <strong>{{ $ticket->subject }}</strong>
                        </div>
                        
                        <span>{{ \Illuminate\Support\Str::limit($ticket->message, 75) }}</span>
                    </div>

                    <div>{{ $ticket->user->name_user ?? 'Usuario' }}</div>

                    <div>{{ $ticket->branch->name_branch ?? 'Sin sucursal' }}</div>

                    <div>
                        <span class="support-status support-status-{{ strtolower($ticket->status) }}">
                            {{ ucfirst($ticket->status) }}
                        </span>
                    </div>

                    <div>
                        @if(($ticket->unread_messages_count ?? 0) > 0)
                            <span class="support-badge support-badge-alert">
                                ❗ {{ $ticket->unread_messages_count }} nuevo(s)
                            </span>
                        @else
                            <span class="support-muted">Sin nuevos</span>
                        @endif
                    </div>

                    <div>{{ optional($ticket->created_at)->format('d/m/Y H:i') }}</div>

                    <div>
                        <a href="{{ route('developer.support.show', $ticket->id) }}" class="support-btn">
                            Abrir
                        </a>
                    </div>
                </div>
            @empty
                <div class="support-empty">
                    <div class="support-empty-icon">📭</div>
                    <h3>No hay tickets de soporte</h3>
                    <p>Cuando un usuario envíe un mensaje, aparecerá aquí.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection