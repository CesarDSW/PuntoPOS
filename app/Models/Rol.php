<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rol';
    protected $primaryKey = 'rol_id';
    public $timestamps = false;

    protected $fillable = [
        'type_rol'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'rol_idfk', 'rol_id');
    }
}
