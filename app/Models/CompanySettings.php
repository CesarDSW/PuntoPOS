<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySettings extends Model
{
    protected $table = 'company_setting';
    protected $primaryKey = 'company_setting_id';
    public $timestamps = false;

    protected $fillable = [
        'company_idfk',
        'notify_low_stock',
        'notify_sale_cancelled',
        'notify_out_of_stock',
        'language',
        'timezone',
        'date_format',
        'time_format',
        'auto_print',
        'show_taxes',
        'printer_width',
        'theme',
        'price_decimals',
    ];
}
