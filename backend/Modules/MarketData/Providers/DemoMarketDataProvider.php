<?php

namespace Modules\MarketData\Providers;

use Carbon\CarbonInterface;
use Modules\MarketData\Contracts\MarketDataProviderInterface;
use Modules\MarketData\DTOs\MarketBarData;

/**
 * Placeholder demo bars for contract/tests only — not real market data.
 */
class DemoMarketDataProvider implements MarketDataProviderInterface
{
    public function providerName(): string
    {
        return 'demo';
    }

    /** @return iterable<MarketBarData> */
    public function fetchDailyBars(string $symbol, CarbonInterface $from, CarbonInterface $to): iterable
    {
        yield new MarketBarData(
            startedAt: $from->copy()->startOfDay(),
            open: '10000.0000',
            high: '10100.0000',
            low: '9900.0000',
            close: '10050.0000',
            volume: 1000,
            isAdjusted: false,
        );
    }

    /** @return iterable<MarketBarData> */
    public function fetchIntradayBars(string $symbol, CarbonInterface $date, string $timeframe = '5m'): iterable
    {
        yield new MarketBarData(
            startedAt: $date->copy()->setTime(9, 0),
            open: '10000.0000',
            high: '10020.0000',
            low: '9990.0000',
            close: '10010.0000',
            volume: 100,
            isAdjusted: false,
        );
    }
}
