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
        'status_subscription',
        'status',
        'plan',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'status_subscription' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_idfk', 'userr_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_idfk', 'company_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'activa'
            && $this->status_subscription === true
            && (!$this->end_date || $this->end_date->isFuture() || $this->end_date->isToday());
    }
}