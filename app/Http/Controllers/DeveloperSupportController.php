<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class DeveloperSupportController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $tickets = SupportTicket::with(['user', 'branch'])
            ->withCount([
                'messages as unread_messages_count' => function ($query) use ($user) {
                    $query->whereNull('read_at')
                        ->where('sender_id', '!=', $user->userr_id);
                },
            ])
            ->latest()
            ->get();

        $totalTickets = $tickets->count();
        $openTickets = $tickets->where('status', 'open')->count();
        $answeredTickets = $tickets->where('status', 'answered')->count();
        $closedTickets = $tickets->where('status', 'closed')->count();
        $unreadTickets = $tickets->where('unread_messages_count', '>', 0)->count();

        return view('developer.support.index', compact(
            'tickets',
            'totalTickets',
            'openTickets',
            'answeredTickets',
            'closedTickets',
            'unreadTickets'
        ));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'branch', 'messages.sender']);

        SupportTicketMessage::where('support_ticket_id', $ticket->id)
            ->where('sender_id', '!=', auth()->user()->userr_id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return view('developer.support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => auth()->user()->userr_id,
            'message' => $request->message,
        ]);

        $ticket->update([
            'status' => 'answered',
        ]);

        app(NotificationService::class)->supportTicketAnswered($ticket);

        return redirect()
            ->route('developer.support.show', $ticket)
            ->with('success', 'Respuesta enviada correctamente.');
    }

    public function close(SupportTicket $ticket)
    {
        $ticket->update([
            'status' => 'closed',
        ]);

        return redirect()
            ->route('developer.support.index')
            ->with('success', 'Ticket cerrado correctamente.');
    }
}