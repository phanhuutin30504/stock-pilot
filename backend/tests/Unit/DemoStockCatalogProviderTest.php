<?php

namespace Tests\Unit;

use Modules\Stock\DTOs\StockData;
use Modules\Stock\Providers\DemoStockCatalogProvider;
use Tests\TestCase;

class DemoStockCatalogProviderTest extends TestCase
{
    public function test_it_returns_expected_demo_stock_dtos(): void
    {
        $provider = new DemoStockCatalogProvider;
        $stocks = iterator_to_array($provider->fetchStocks());

        $this->assertSame('demo', $provider->providerName());
        $this->assertCount(5, $stocks);
        $this->assertContainsOnlyInstancesOf(StockData::class, $stocks);

        $symbols = array_map(static fn (StockData $s) => $s->normalizedSymbol(), $stocks);
        $this->assertSame(['FPT', 'PVD', 'HPG', 'SSI', 'VNM'], $symbols);
    }
}
