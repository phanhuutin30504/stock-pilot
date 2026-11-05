<?php

namespace Modules\Stock\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MarketData\Models\StockBar;

class Stock extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'exchange',
        'industry_id',
        'company_name',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'industry_id' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function bars(): HasMany
    {
        return $this->hasMany(StockBar::class);
    }
}
