<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'subscription';
    protected $primaryKey = 'subscription_id';

    public $timestamps = false;

    protected $fillable = [
        'user_idfk',
        'company_idfk',
        'stripe_customer_id',
        'stripe_subscription_id',
        'status',
        'plan',
        'start_date',
        'end_date'
    ];
}