<?php

namespace Modules\MarketData\Models;

use Illuminate\Database\Eloquent\Model;

class MarketIndex extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'exchange',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
