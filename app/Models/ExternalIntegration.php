<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalIntegration extends Model
{
    protected $table = 'external_integrations';

    protected $fillable = [
        'company_idfk',
        'branch_idfk',
        'userr_idfk',
        'source_app',
        'external_user_id',
        'external_base_url',
        'access_token',
        'status',
        'last_products_sync_at',
        'last_sales_sync_at',
    ];

    protected $hidden = [
        'access_token',
    ];

    protected $casts = [
        'company_idfk' => 'integer',
        'branch_idfk' => 'integer',
        'userr_idfk' => 'integer',
        'external_user_id' => 'integer',
        'last_products_sync_at' => 'datetime',
        'last_sales_sync_at' => 'datetime',
    ];

    public function syncMaps(): HasMany 
    {
        return $this->hasMany(ExternalSyncMap::class, 'external_integration_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getProductsEndpointAttribute(): string
    {
        return rtrim($this->external_base_url, '/') . '/punto_products';
    }

    public function getSalesEndpointAttribute(): string
    {
        return rtrim($this->external_base_url, '/') . '/punto_sales';
    }
}
