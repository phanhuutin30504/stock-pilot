<?php

namespace Modules\Stock\Providers;

use Modules\Stock\Contracts\StockCatalogProviderInterface;
use Modules\Stock\DTOs\StockData;

/**
 * Demo catalog only — not a complete VN market universe or realtime feed.
 */
class DemoStockCatalogProvider implements StockCatalogProviderInterface
{
    public function providerName(): string
    {
        return 'demo';
    }

    /** @return iterable<StockData> */
    public function fetchStocks(): iterable
    {
        $rows = [
            ['symbol' => 'FPT', 'name' => 'FPT Corporation', 'exchange' => 'HOSE', 'companyName' => 'FPT Corporation'],
            ['symbol' => 'PVD', 'name' => 'PetroVietnam Drilling', 'exchange' => 'HOSE', 'companyName' => 'PV Drilling & Well Services'],
            ['symbol' => 'HPG', 'name' => 'Hoa Phat Group', 'exchange' => 'HOSE', 'companyName' => 'Hoa Phat Group Joint Stock Company'],
            ['symbol' => 'SSI', 'name' => 'SSI Securities', 'exchange' => 'HOSE', 'companyName' => 'SSI Securities Corporation'],
            ['symbol' => 'VNM', 'name' => 'Vinamilk', 'exchange' => 'HOSE', 'companyName' => 'Vietnam Dairy Products Joint Stock Company'],
        ];

        foreach ($rows as $row) {
            yield StockData::fromArray($row);
        }
    }
}
