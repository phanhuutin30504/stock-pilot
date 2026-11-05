<?php

namespace Tests\Feature;

use Modules\Stock\Models\Stock;
use Modules\Stock\Services\StockCatalogSyncService;
use Tests\TestCase;

class StockCatalogSyncServiceTest extends TestCase
{
    public function test_sync_is_idempotent_and_does_not_duplicate(): void
    {
        /** @var StockCatalogSyncService $service */
        $service = $this->app->make(StockCatalogSyncService::class);

        $first = $service->sync();
        $second = $service->sync();

        $this->assertSame('success', $first->status);
        $this->assertSame(5, $first->inserted);
        $this->assertSame(0, $second->inserted);
        $this->assertSame(5, Stock::query()->count());
        $this->assertSame(5, Stock::query()->distinct('symbol')->count('symbol'));
    }

    public function test_sync_does_not_deactivate_missing_symbols(): void
    {
        Stock::query()->create([
            'symbol' => 'AAA',
            'name' => 'Extra Stock',
            'exchange' => 'HOSE',
            'is_active' => true,
        ]);

        /** @var StockCatalogSyncService $service */
        $service = $this->app->make(StockCatalogSyncService::class);
        $service->sync();

        $extra = Stock::query()->where('symbol', 'AAA')->first();

        $this->assertNotNull($extra);
        $this->assertTrue($extra->is_active);
        $this->assertSame(6, Stock::query()->count());
    }
}
