<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicketMessage extends Model
{
       protected $table = 'support_ticket_messages';

    protected $fillable = [
        'support_ticket_id',
        'sender_id',
        'message',
        'read_at',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'userr_id');
    }
}
