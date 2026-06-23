<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $table = 'sale';
    protected $primary_key = 'sale_id';

    public $incrementing = true;

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
        'payment_status',
        'stripe_id',
    ];

    public function payment()
    {
        return $this->hasOne(\App\Models\Payment::class, 'sale_idfk', 'sale_id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_idfk', 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(\App\Models\SaleDetail::class, 'sale_idfk', 'sale_id');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_idfk', 'id');
    }
}
