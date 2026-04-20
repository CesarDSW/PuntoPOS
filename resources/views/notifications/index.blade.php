@extends('layout.dashboard_design')

@section('content')
    <div class="settings-card">
        <h2>Notificaciones del sistema</h2>

        @forelse($notifications as $notification)
            <div class="settings-option-card" style="margin-bottom: 12px;">
                <div>
                    <h3>{{ $notification->title }}</h3>
                    <p>{{ $notification->message }}</p>
                    <small>{{ $notification->created_at }}</small>
                </div>
            </div>
        @empty
            <p>No hay notificaciones registradas.</p>
        @endforelse
    </div>
@endsection