<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    protected $table = 'system_notification';
    protected $primaryKey = 'notification_id';

    public $timestamps = false;

    protected $fillable = [
        'company_idfk',
        'branch_idfk',
        'target_user_idfk',
        'type_code',
        'title',
        'message',
        'reference_type',
        'reference_id',
        'action_url',
        'dedupe_key',
        'is_read',
        'created_at',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'read_at' => 'datetime',
    ];
}
