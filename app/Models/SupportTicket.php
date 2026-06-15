<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Branch;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'subject',
        'message',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'userr_id'
        );
    }

public function branch()
{
    return $this->belongsTo(
        Branch::class,
        'branch_id',
        'branch_id'
    );
}
    
}