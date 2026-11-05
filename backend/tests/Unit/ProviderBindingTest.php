<?php

namespace Tests\Unit;

use Modules\Shared\Exceptions\UnsupportedProviderException;
use Modules\Stock\Contracts\StockCatalogProviderInterface;
use Modules\Stock\Providers\DemoStockCatalogProvider;
use Tests\TestCase;

class ProviderBindingTest extends TestCase
{
    public function test_it_resolves_demo_provider_from_config(): void
    {
        config(['stockpilot.provider' => 'demo']);

        $provider = $this->app->make(StockCatalogProviderInterface::class);

        $this->assertInstanceOf(DemoStockCatalogProvider::class, $provider);
        $this->assertSame('demo', $provider->providerName());
    }

    public function test_it_fails_fast_for_unsupported_provider(): void
    {
        config(['stockpilot.provider' => 'unknown-provider']);

        $this->expectException(UnsupportedProviderException::class);

        $this->app->make(StockCatalogProviderInterface::class);
    }
}
