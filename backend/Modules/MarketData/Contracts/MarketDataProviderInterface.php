<?php

namespace Modules\MarketData\Contracts;

use Carbon\CarbonInterface;
use Modules\MarketData\DTOs\MarketBarData;

interface MarketDataProviderInterface
{
    /** @return iterable<MarketBarData> */
    public function fetchDailyBars(string $symbol, CarbonInterface $from, CarbonInterface $to): iterable;

    /** @return iterable<MarketBarData> */
    public function fetchIntradayBars(string $symbol, CarbonInterface $date, string $timeframe = '5m'): iterable;

    public function providerName(): string;
}
