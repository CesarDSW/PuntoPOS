{{-- resources/views/developer/support/show.blade.php --}}
@extends('layout.dashboard_design')

@section('title', 'Ticket de soporte')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/tickets/support.css') }}">
@endpush

@section('content')
<div class="support-page">
    <div class="support-detail-layout">
        <div class="support-detail-main">
            <div class="support-chat-card">
                <div class="support-chat-header">
                    <div>
                        <span class="support-eyebrow">Ticket #{{ $ticket->id }}</span>
                        <h1>{{ $ticket->subject }}</h1>
                        <p>
                            Conversación entre el usuario y soporte.
                        </p>
                    </div>

                    <div class="support-chat-header-actions">
                        <span class="support-status support-status-{{ strtolower($ticket->status) }}">
                            {{ ucfirst($ticket->status) }}
                        </span>

                        <a href="{{ route('developer.support.index') }}" class="support-btn-secondary">
                            Volver
                        </a>
                    </div>
                </div>

                <div class="support-chat-messages">
                    @forelse($ticket->messages as $message)
                        @php
                            $isMine = $message->sender_id === auth()->user()->userr_id;
                        @endphp

                        <div class="support-message {{ $isMine ? 'support-message-mine' : 'support-message-other' }}">
                            <div class="support-message-box">
                                <div class="support-message-head">
                                    <strong>{{ $message->sender->name_user ?? 'Usuario' }}</strong>
                                    <span>{{ optional($message->created_at)->format('d/m/Y H:i') }}</span>
                                </div>

                                <p>{{ $message->message }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="support-empty support-empty-chat">
                            <div class="support-empty-icon">💬</div>
                            <h3>Sin mensajes</h3>
                            <p>Este ticket todavía no tiene conversación.</p>
                        </div>
                    @endforelse
                </div>

                @if($ticket->status !== 'closed')
                    <form action="{{ route('developer.support.reply', $ticket->id) }}" method="POST" class="support-reply-form">
                        @csrf

                        <label for="message">Responder ticket</label>
                        <textarea
                            name="message"
                            id="message"
                            rows="5"
                            placeholder="Escribe una respuesta clara para el usuario..."
                            required
                        ></textarea>

                        <div class="support-actions">
                            <button type="submit" class="support-btn">
                                Enviar respuesta
                            </button>
                        </div>
                    </form>
                @else
                    <div class="support-closed">
                        Este ticket ya fue cerrado.
                    </div>
                @endif
            </div>
        </div>

        <aside class="support-detail-side">
            <div class="support-info-card">
                <h3>Información del ticket</h3>

                <div class="support-info-list">
                    <div class="support-info-item">
                        <span>ID</span>
                        <strong>#{{ $ticket->id }}</strong>
                    </div>

                    <div class="support-info-item">
                        <span>Usuario</span>
                        <strong>{{ $ticket->user->name_user ?? 'Usuario' }}</strong>
                    </div>

                    <div class="support-info-item">
                        <span>Correo</span>
                        <strong>{{ $ticket->user->email ?? 'Sin correo' }}</strong>
                    </div>

                    <div class="support-info-item">
                        <span>Sucursal</span>
                        <strong>{{ $ticket->branch->name_branch ?? 'Sin sucursal' }}</strong>
                    </div>

                    <div class="support-info-item">
                        <span>Estado</span>
                        <strong>{{ ucfirst($ticket->status) }}</strong>
                    </div>

                    <div class="support-info-item">
                        <span>Creado</span>
                        <strong>{{ optional($ticket->created_at)->format('d/m/Y H:i') }}</strong>
                    </div>

                    <div class="support-info-item">
                        <span>Mensajes</span>
                        <strong>{{ $ticket->messages->count() }}</strong>
                    </div>
                </div>

                @if($ticket->status !== 'closed')
                    <form action="{{ route('developer.support.close', $ticket->id) }}" method="POST" class="support-close-form">
                        @csrf
                        <button type="submit" class="support-btn-danger">
                            Cerrar ticket
                        </button>
                    </form>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection