<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MarketData\Models\MarketIndex;

class MarketIndexSeeder extends Seeder
{
    public function run(): void
    {
        // Common public index symbols for VN market scaffolding (not provider API codes).
        $indices = [
            ['symbol' => 'VNINDEX', 'name' => 'VN-Index', 'exchange' => 'HOSE'],
            ['symbol' => 'VN30', 'name' => 'VN30 Index', 'exchange' => 'HOSE'],
            ['symbol' => 'HNXINDEX', 'name' => 'HNX Index', 'exchange' => 'HNX'],
            ['symbol' => 'UPCOMINDEX', 'name' => 'UPCOM Index', 'exchange' => 'UPCOM'],
        ];

        foreach ($indices as $index) {
            MarketIndex::query()->updateOrCreate(
                ['symbol' => $index['symbol']],
                [
                    'name' => $index['name'],
                    'exchange' => $index['exchange'],
                    'is_active' => true,
                ]
            );
        }
    }
}
