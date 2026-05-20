<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionGrant extends Model
{
    protected $table = 'permission_grant';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'grant_rol_idfk',
        'permission_idfk',
    ];
}
