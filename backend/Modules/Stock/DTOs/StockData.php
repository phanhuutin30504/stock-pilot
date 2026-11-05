<?php

namespace Modules\Stock\DTOs;

use Modules\Shared\Exceptions\InvalidStockDataException;

final readonly class StockData
{
    public function __construct(
        public string $symbol,
        public string $name,
        public string $exchange,
        public ?string $companyName = null,
        public ?string $industryCode = null,
    ) {
        $this->assertValid();
    }

    /**
     * @param  array{symbol:string,name:string,exchange:string,company_name?:?string,companyName?:?string,industry_code?:?string,industryCode?:?string}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            symbol: (string) ($payload['symbol'] ?? ''),
            name: (string) ($payload['name'] ?? ''),
            exchange: (string) ($payload['exchange'] ?? ''),
            companyName: self::nullableString($payload['company_name'] ?? $payload['companyName'] ?? null),
            industryCode: self::nullableString($payload['industry_code'] ?? $payload['industryCode'] ?? null),
        );
    }

    /**
     * Attributes suitable for Stock model persistence (without industry_id).
     *
     * @return array{symbol:string,name:string,exchange:string,company_name:?string,is_active:bool}
     */
    public function toPersistenceArray(): array
    {
        return [
            'symbol' => $this->normalizedSymbol(),
            'name' => trim($this->name),
            'exchange' => $this->normalizedExchange(),
            'company_name' => $this->companyName !== null ? trim($this->companyName) : null,
            'is_active' => true,
        ];
    }

    public function normalizedSymbol(): string
    {
        return strtoupper(trim($this->symbol));
    }

    public function normalizedExchange(): string
    {
        return strtoupper(trim($this->exchange));
    }

    public function normalizedIndustryCode(): ?string
    {
        if ($this->industryCode === null) {
            return null;
        }

        $code = strtoupper(trim($this->industryCode));

        return $code === '' ? null : $code;
    }

    private function assertValid(): void
    {
        if (trim($this->symbol) === '') {
            throw new InvalidStockDataException('Stock symbol is required.');
        }

        if (trim($this->name) === '') {
            throw new InvalidStockDataException('Stock name is required.');
        }

        if (trim($this->exchange) === '') {
            throw new InvalidStockDataException('Stock exchange is required.');
        }

        $exchange = $this->normalizedExchange();
        $allowed = config('stockpilot.exchanges', ['HOSE', 'HNX', 'UPCOM']);

        if (! in_array($exchange, $allowed, true)) {
            throw new InvalidStockDataException("Unsupported exchange [{$exchange}].");
        }
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
