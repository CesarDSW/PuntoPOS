<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportTicket;

class SupportController extends Controller
{

    /* GUARDAR TICKET */

    public function ticket(Request $request)
    {

        $request->validate([

            'subject' => 'required',
            'message' => 'required',

        ]);

        SupportTicket::create([

            // USUARIO LOGEADO
            'user_id' => auth()->id(),

            // DATOS
            'subject' => $request->subject,

            'message' => $request->message,

            // ESTADO
            'status' => 'pendiente'

        ]);

        return back()->with(

            'success',
            'Mensaje enviado correctamente'

        );

    }

    /* LISTAR TICKETS */
      
    public function index()
    {
  
         
        $tickets = SupportTicket::latest()->get();

        return view(

            'support.index',
            compact('tickets')

        );

    }

    /* MARCAR COMO ATENDIDO */

    public function completar($id)
    {

        $ticket = SupportTicket::findOrFail($id);

        $ticket->status = 'atendido';

        $ticket->save();

        return back();

    }

}