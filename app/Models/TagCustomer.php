<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagCustomer extends Model
{
    protected $table = 'tag_customer';
    protected $primaryKey = 'tag_customer_id';
    public $timestamps = false;

    protected $fillable = [
        'customer_idfk',
        'tag_idfk',
    ];
}
