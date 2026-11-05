<?php

return [
    'timezone' => env('APP_TIMEZONE', 'Asia/Ho_Chi_Minh'),

    'provider' => env('MARKET_DATA_PROVIDER', 'demo'),

    'sync' => [
        'enabled' => (bool) env('MARKET_DATA_SYNC_ENABLED', false),
        'batch_size' => (int) env('MARKET_DATA_SYNC_BATCH_SIZE', 50),
    ],

    'exchanges' => ['HOSE', 'HNX', 'UPCOM'],

    'timeframes' => ['1d', '5m'],

    /*
    |--------------------------------------------------------------------------
    | Provider class map (fail-fast for unsupported names)
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'demo' => \Modules\Stock\Providers\DemoStockCatalogProvider::class,
        // 'ssi' => \Modules\MarketData\Providers\SSI\SsiStockCatalogProvider::class,
        // 'dnse' => \Modules\MarketData\Providers\DNSE\DnseStockCatalogProvider::class,
    ],
];
