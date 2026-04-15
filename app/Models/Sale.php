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

}
