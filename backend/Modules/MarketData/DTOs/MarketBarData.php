<?php

namespace Modules\MarketData\DTOs;

use Carbon\CarbonInterface;
use Modules\Shared\Exceptions\InvalidMarketBarDataException;

final readonly class MarketBarData
{
    public function __construct(
        public CarbonInterface $startedAt,
        public string $open,
        public string $high,
        public string $low,
        public string $close,
        public int $volume,
        public bool $isAdjusted = false,
    ) {
        $this->assertValid();
    }

    /**
     * @return array{
     *     trading_date:string,
     *     started_at:string,
     *     open:string,
     *     high:string,
     *     low:string,
     *     close:string,
     *     volume:int,
     *     is_adjusted:bool
     * }
     */
    public function toPersistenceArray(): array
    {
        return [
            'trading_date' => $this->startedAt->toDateString(),
            'started_at' => $this->startedAt->toIso8601String(),
            'open' => $this->normalizeDecimal($this->open),
            'high' => $this->normalizeDecimal($this->high),
            'low' => $this->normalizeDecimal($this->low),
            'close' => $this->normalizeDecimal($this->close),
            'volume' => $this->volume,
            'is_adjusted' => $this->isAdjusted,
        ];
    }

    private function assertValid(): void
    {
        foreach (['open' => $this->open, 'high' => $this->high, 'low' => $this->low, 'close' => $this->close] as $field => $value) {
            if (! is_numeric($value)) {
                throw new InvalidMarketBarDataException("Market bar {$field} must be numeric.");
            }

            if (bccomp($this->normalizeDecimal((string) $value), '0', 4) !== 1) {
                throw new InvalidMarketBarDataException("Market bar {$field} must be positive.");
            }
        }

        if ($this->volume < 0) {
            throw new InvalidMarketBarDataException('Market bar volume must not be negative.');
        }

        $open = $this->normalizeDecimal($this->open);
        $high = $this->normalizeDecimal($this->high);
        $low = $this->normalizeDecimal($this->low);
        $close = $this->normalizeDecimal($this->close);

        if (bccomp($high, $open, 4) < 0 || bccomp($high, $close, 4) < 0) {
            throw new InvalidMarketBarDataException('High must be greater than or equal to open and close.');
        }

        if (bccomp($low, $open, 4) > 0 || bccomp($low, $close, 4) > 0) {
            throw new InvalidMarketBarDataException('Low must be less than or equal to open and close.');
        }

        if (bccomp($high, $low, 4) < 0) {
            throw new InvalidMarketBarDataException('High must be greater than or equal to low.');
        }
    }

    private function normalizeDecimal(string $value): string
    {
        return bcadd(trim($value), '0', 4);
    }
}
