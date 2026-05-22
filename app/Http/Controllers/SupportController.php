<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportTicket;

class SupportController extends Controller
{

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

}