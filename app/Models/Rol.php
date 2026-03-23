<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rol';
    protected $primarykey = 'rol_id';

    protected $fillable = [
        'type_rol'
    ];
}
