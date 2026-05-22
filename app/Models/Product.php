<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'productt';
    protected $primaryKey = 'product_id';

    public $incrementing = true;   // 🔥 IMPORTANTE
    protected $keyType = 'int';    // 🔥 IMPORTANTE
    public $timestamps = false;

    protected $fillable = [
        'name',
        'price',
        'stock'
    ];

    // 🔥 Relación (opcional)
    public function saleItems()
    {
        return $this->hasMany(SaleDetail::class, 'product_idfk', 'product_id');
    }
}