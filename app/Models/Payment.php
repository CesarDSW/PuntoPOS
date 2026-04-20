<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $primaryKey = 'payment_id';

    public $timestamps = false; // si no usas created_at

    protected $fillable = [
        'payment_method',
        'sale_idfk',
        'customer_idfk'
    ];
}