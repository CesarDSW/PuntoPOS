<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'productt';

    protected $fillable = [
        'name',
        'price',
        'stock'
    ];
}