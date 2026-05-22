<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    protected $table = 'saleitem';
    protected $primaryKey = 'saleitem_id';

    public $timestamps = false;

    protected $fillable = [
        'sale_idfk',
        'product_idfk', // 🔥 AGREGA ESTO
        'item_name',
        'amount',
        'unit_price',
        'total_line'
    ];

    // 🔥 Relación con venta
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_idfk', 'sale_id');
    }

    // 🔥 Relación con producto
public function product()
{
    return $this->belongsTo(\App\Models\Product::class, 'product_idfk');
}
}