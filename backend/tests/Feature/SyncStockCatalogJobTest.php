<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Modules\Stock\Jobs\SyncStockCatalogJob;
use Modules\Stock\Models\Stock;
use Tests\TestCase;

class SyncStockCatalogJobTest extends TestCase
{
    public function test_job_can_be_dispatched(): void
    {
        Queue::fake();

        SyncStockCatalogJob::dispatch();

        Queue::assertPushed(SyncStockCatalogJob::class);
    }

    public function test_job_syncs_demo_catalog_when_handled(): void
    {
        (new SyncStockCatalogJob)->handle($this->app->make(\Modules\Stock\Services\StockCatalogSyncService::class));

        $this->assertSame(5, Stock::query()->count());
        $this->assertTrue(Stock::query()->where('symbol', 'FPT')->exists());
    }
}
