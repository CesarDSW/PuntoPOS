<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';
    protected $primaryKey = 'customer_id';
    public $timestamps = false;

     protected $fillable = [
        'name_customer',
        'phone',
        'email',
        'company_idfk',
        'status_customer',
    ];
}
