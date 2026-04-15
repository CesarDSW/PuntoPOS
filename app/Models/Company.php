<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'company';
    protected $primaryKey = 'company_id';

    public $timestamps = false;

    protected $fillable = [
        'name_company',
        'rfc',
        'city',
        'address',
        'city',
        'state',
        'zip_code',
        'phone',
        'email',
        'currency',
        'logo',
        'opening_time',
        'closing_time',
        'description_company',
        'payment_methods',
        'onboarding_completed',
        'owner_user_id',
    ];
}
