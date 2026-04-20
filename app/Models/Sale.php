<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $table = 'sale';
    protected $primary_key = 'sale_id';

    protected $fillable = [
        'date_time',
        'company_idfk',
        'branch_idfk',
        'cashier_userr_idfk',
        'customer_idfk',
        'subtotal',
        'discount',
        'total',
        'status_sale',
    ];
     public function payment()
{
    return $this->hasOne(\App\Models\Payment::class, 'sale_idfk', 'sale_id');
}
public function customer()
{
    return $this->belongsTo(\App\Models\Customer::class, 'customer_idfk', 'customer_id');
}
}
