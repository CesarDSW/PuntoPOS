<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permission';
    protected $primaryKey = 'permission_id';
    public $timestamps = false;

    protected $fillable = [
        'code_permission',
        'description_permission',
    ];
}
