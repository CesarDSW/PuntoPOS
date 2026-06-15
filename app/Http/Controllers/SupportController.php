<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportTicket;

class SupportController extends Controller
{

    /* =====================================
       GUARDAR TICKET
    ===================================== */

    public function ticket(Request $request)
    {

        $request->validate([

            'subject' => 'required',

            'message' => 'required',

        ]);

        SupportTicket::create([

            'user_id'   => auth()->id(),

            'branch_id' => 1,

            'subject'   => $request->subject,

            'message'   => $request->message,

            'status'    => 'pendiente'

        ]);

        return back()->with(

            'success',

            'Mensaje enviado correctamente'

        );

    }

    /* =====================================
       LISTAR TICKETS
    ===================================== */

    public function index(Request $request)
    {

        $search = $request->search;

        $status = $request->status;

        $tickets = SupportTicket::with([
            'user',
            'branch'
        ])

        ->when($search, function ($query) use ($search) {

            $query->where(function ($q) use ($search) {

                $q->where(
                    'subject',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'message',
                    'like',
                    "%{$search}%"
                );

            });

        })

        ->when($status, function ($query) use ($status) {

            $query->where(
                'status',
                $status
            );

        })

        ->latest()

        ->paginate(10);

        $totalTickets = SupportTicket::count();

        $totalPendientes = SupportTicket::where(
            'status',
            'pendiente'
        )->count();

        $totalAtendidos = SupportTicket::where(
            'status',
            'atendido'
        )->count();

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

    /* =====================================
       MARCAR COMO ATENDIDO
    ===================================== */

    public function completar($id)
    {

        $ticket = SupportTicket::findOrFail($id);

        $ticket->status = 'atendido';

        $ticket->save();

        return back()->with(

            'success',

            'Ticket marcado como atendido'

        );

    }

}
