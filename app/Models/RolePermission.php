<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $table = 'role_permission';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'rol_idfk',
        'permission_idfk',
        'allow',
    ];
}
