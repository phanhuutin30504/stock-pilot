<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\MarketData\Contracts\MarketDataProviderInterface;
use Modules\MarketData\Providers\DemoMarketDataProvider;
use Modules\Shared\Exceptions\UnsupportedProviderException;
use Modules\Stock\Contracts\StockCatalogProviderInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StockCatalogProviderInterface::class, function () {
            $name = (string) config('stockpilot.provider', 'demo');
            $map = config('stockpilot.providers', []);

            if (! isset($map[$name]) || ! is_string($map[$name])) {
                throw new UnsupportedProviderException("Unsupported market data provider [{$name}].");
            }

            $class = $map[$name];

            if (! class_exists($class)) {
                throw new UnsupportedProviderException("Provider class [{$class}] does not exist.");
            }

            $provider = $this->app->make($class);

            if (! $provider instanceof StockCatalogProviderInterface) {
                throw new UnsupportedProviderException("Provider [{$name}] must implement StockCatalogProviderInterface.");
            }

            return $provider;
        });

        $this->app->bind(MarketDataProviderInterface::class, function () {
            $name = (string) config('stockpilot.provider', 'demo');

            return match ($name) {
                'demo' => $this->app->make(DemoMarketDataProvider::class),
                default => throw new UnsupportedProviderException("Unsupported market bar provider [{$name}]."),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
