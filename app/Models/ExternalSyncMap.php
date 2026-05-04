<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ExternalSyncMap extends Model
{
    protected $table = 'external_sync_maps';

    protected  $fillable = [
        'external_integration_id',
        'entity_type',
        'external_id',
        'local_table',
        'local_id',
    ];

    protected $casts = [
        'external_integration_id' => 'integer',
        'local_id' => 'integer',
    ];

    public function integration(): BelongsTo
    {
        return $this->belongsto(ExternalIntegration::class, 'external_integration_id');
    }
}

