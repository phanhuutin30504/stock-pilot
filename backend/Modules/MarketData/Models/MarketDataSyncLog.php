<?php

namespace Modules\MarketData\Models;

use Illuminate\Database\Eloquent\Model;

class MarketDataSyncLog extends Model
{
    protected $fillable = [
        'provider',
        'sync_type',
        'symbol',
        'status',
        'started_at',
        'finished_at',
        'records_count',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'records_count' => 'integer',
            'metadata' => 'array',
        ];
    }
}
