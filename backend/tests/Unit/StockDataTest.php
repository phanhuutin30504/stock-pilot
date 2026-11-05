<?php

namespace Tests\Unit;

use Modules\Shared\Exceptions\InvalidStockDataException;
use Modules\Stock\DTOs\StockData;
use Tests\TestCase;

class StockDataTest extends TestCase
{
    public function test_it_normalizes_symbol_and_exchange_to_uppercase(): void
    {
        $data = StockData::fromArray([
            'symbol' => ' fpt ',
            'name' => 'FPT Corporation',
            'exchange' => 'hose',
        ]);

        $this->assertSame('FPT', $data->normalizedSymbol());
        $this->assertSame('HOSE', $data->normalizedExchange());
        $this->assertSame('FPT', $data->toPersistenceArray()['symbol']);
        $this->assertSame('HOSE', $data->toPersistenceArray()['exchange']);
    }

    public function test_it_rejects_empty_symbol(): void
    {
        $this->expectException(InvalidStockDataException::class);

        StockData::fromArray([
            'symbol' => '   ',
            'name' => 'FPT',
            'exchange' => 'HOSE',
        ]);
    }

    public function test_it_rejects_empty_name(): void
    {
        $this->expectException(InvalidStockDataException::class);

        StockData::fromArray([
            'symbol' => 'FPT',
            'name' => '',
            'exchange' => 'HOSE',
        ]);
    }

    public function test_it_rejects_empty_exchange(): void
    {
        $this->expectException(InvalidStockDataException::class);

        StockData::fromArray([
            'symbol' => 'FPT',
            'name' => 'FPT',
            'exchange' => ' ',
        ]);
    }
}
