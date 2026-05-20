<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permission';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'userr_idfk',
        'permission_idfk',
        'allow',
    ];
}
