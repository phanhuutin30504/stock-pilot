<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Modules\Stock\Jobs\SyncStockCatalogJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| StockPilot schedules (Asia/Ho_Chi_Minh)
|--------------------------------------------------------------------------
|
| Catalog sync: weekdays 18:00 VN time when stockpilot.sync.enabled=true.
| Local single-instance: withoutOverlapping only (no onOneServer).
| Market bars / indicators / signals are intentionally not scheduled in Sprint 1.
|
*/
Schedule::job(new SyncStockCatalogJob)
    ->weekdays()
    ->dailyAt('18:00')
    ->timezone(config('stockpilot.timezone', 'Asia/Ho_Chi_Minh'))
    ->when(fn () => (bool) config('stockpilot.sync.enabled'))
    ->withoutOverlapping()
    ->name('stockpilot-sync-stock-catalog');
