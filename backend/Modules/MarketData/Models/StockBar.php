<?php

namespace Modules\MarketData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Stock\Models\Stock;

class StockBar extends Model
{
    protected $fillable = [
        'stock_id',
        'timeframe',
        'trading_date',
        'started_at',
        'open',
        'high',
        'low',
        'close',
        'volume',
        'is_adjusted',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'stock_id' => 'integer',
            'trading_date' => 'date',
            'started_at' => 'datetime',
            'open' => 'decimal:4',
            'high' => 'decimal:4',
            'low' => 'decimal:4',
            'close' => 'decimal:4',
            'volume' => 'integer',
            'is_adjusted' => 'boolean',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}
