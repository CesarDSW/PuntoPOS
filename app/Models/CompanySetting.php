<?php

namespace App\Models;

// app/Models/CompanySetting.php


use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_idfk',
        'theme',
        'price_decimals'
    ];
}