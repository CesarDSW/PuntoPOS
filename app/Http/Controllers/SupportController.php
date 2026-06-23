<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $user = auth()->user();

        $branchId = session('selected_branch_id')
            ?? session('current_branch_id')
            ?? session('branch_id')
            ?? DB::table('user_branch')
                ->where('userr_idfk', $user->userr_id)
                ->value('sucursal_idfk');

        $ticket = SupportTicket::create([
            'user_id' => $user->userr_id,
            'branch_id' => $branchId,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'open',
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => $user->userr_id,
            'message' => $request->message,
        ]);

        app(NotificationService::class)->supportTicketCreated($ticket);

        return redirect()
            ->back()
            ->with('success', 'Mensaje enviado correctamente.');
    }

    public function ticket(Request $request)
    {
        return $this->store($request);
    }

    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $tickets = SupportTicket::with([
            'user',
            'branch',
        ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);

        $totalTickets = SupportTicket::count();
        $totalPendientes = SupportTicket::where('status', 'open')->count();
        $totalAtendidos = SupportTicket::whereIn('status', ['answered', 'closed'])->count();

        return view(
            'support.index',
            compact(
                'tickets',
                'totalTickets',
                'totalPendientes',
                'totalAtendidos'
            )
        );
    }

    public function completar($id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $ticket->update([
            'status' => 'closed',
        ]);

        return back()->with('success', 'Ticket marcado como atendido.');
    }

    public function myTickets()
    {
        $user = auth()->user();

        $tickets = SupportTicket::withCount([
            'messages as unread_messages_count' => function ($query) use ($user) {
                $query->whereNull('read_at')
                    ->where('sender_id', '!=', $user->userr_id);
            },
        ])
            ->where('user_id', $user->userr_id)
            ->latest()
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'subject' => $ticket->subject,
                    'message' => $ticket->message,
                    'status' => $ticket->status,
                    'unread_messages_count' => $ticket->unread_messages_count,
                    'created_at' => optional($ticket->created_at)->format('d/m/Y H:i'),
                ];
            });

        return response()->json([
            'tickets' => $tickets,
        ]);
    }

    public function conversation(SupportTicket $ticket)
    {
        $user = auth()->user();

        if ((int) $ticket->user_id !== (int) $user->userr_id) {
            abort(403);
        }

        $ticket->load(['messages.sender']);

        SupportTicketMessage::where('support_ticket_id', $ticket->id)
            ->where('sender_id', '!=', $user->userr_id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return response()->json([
            'ticket' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'status' => $ticket->status,
                'created_at' => optional($ticket->created_at)->format('d/m/Y H:i'),
            ],
            'messages' => $ticket->messages->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name_user ?? 'Usuario',
                    'is_mine' => (int) $message->sender_id === (int) $user->userr_id,
                    'message' => $message->message,
                    'created_at' => optional($message->created_at)->format('d/m/Y H:i'),
                ];
            }),
        ]);
    }

    public function replyUser(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $user = auth()->user();

        if ((int) $ticket->user_id !== (int) $user->userr_id) {
            abort(403);
        }

        if ($ticket->status === 'closed') {
            return response()->json([
                'message' => 'Este ticket ya fue cerrado.',
            ], 422);
        }

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => $user->userr_id,
            'message' => $request->message,
        ]);

        $ticket->update([
            'status' => 'open',
        ]);

        app(NotificationService::class)->supportTicketUserReplied($ticket);

        return response()->json([
            'message' => 'Mensaje enviado correctamente.',
        ]);
    }
}