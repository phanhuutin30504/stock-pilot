<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Modules\MarketData\DTOs\MarketBarData;
use Modules\Shared\Exceptions\InvalidMarketBarDataException;
use Tests\TestCase;

class MarketBarDataTest extends TestCase
{
    public function test_it_accepts_valid_ohlc(): void
    {
        $bar = new MarketBarData(
            startedAt: Carbon::parse('2026-11-05 09:00:00', 'Asia/Ho_Chi_Minh'),
            open: '10000',
            high: '10500',
            low: '9900',
            close: '10200',
            volume: 1000,
        );

        $attrs = $bar->toPersistenceArray();

        $this->assertSame('10000.0000', $attrs['open']);
        $this->assertSame('10500.0000', $attrs['high']);
        $this->assertSame(1000, $attrs['volume']);
    }

    public function test_it_rejects_high_below_open(): void
    {
        $this->expectException(InvalidMarketBarDataException::class);

        new MarketBarData(
            startedAt: Carbon::parse('2026-11-05'),
            open: '10000',
            high: '9000',
            low: '8000',
            close: '9500',
            volume: 1,
        );
    }

    public function test_it_rejects_negative_volume(): void
    {
        $this->expectException(InvalidMarketBarDataException::class);

        new MarketBarData(
            startedAt: Carbon::parse('2026-11-05'),
            open: '10000',
            high: '10100',
            low: '9900',
            close: '10050',
            volume: -1,
        );
    }

    public function test_it_rejects_non_positive_price(): void
    {
        $this->expectException(InvalidMarketBarDataException::class);

        new MarketBarData(
            startedAt: Carbon::parse('2026-11-05'),
            open: '0',
            high: '1',
            low: '0.5',
            close: '1',
            volume: 1,
        );
    }
}
