<?php

namespace Modules\Stock\Contracts;

use Modules\Stock\DTOs\StockData;

interface StockCatalogProviderInterface
{
    /** @return iterable<StockData> */
    public function fetchStocks(): iterable;

    public function providerName(): string;
}
