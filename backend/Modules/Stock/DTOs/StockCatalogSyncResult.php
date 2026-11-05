<?php

namespace Modules\Stock\DTOs;

final readonly class StockCatalogSyncResult
{
    public function __construct(
        public string $provider,
        public int $fetched,
        public int $inserted,
        public int $updated,
        public int $unchanged,
        public string $status = 'success',
        public ?string $errorMessage = null,
    ) {
    }

    public function processed(): int
    {
        return $this->inserted + $this->updated + $this->unchanged;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'fetched' => $this->fetched,
            'inserted' => $this->inserted,
            'updated' => $this->updated,
            'unchanged' => $this->unchanged,
            'processed' => $this->processed(),
            'status' => $this->status,
            'error_message' => $this->errorMessage,
        ];
    }
}
