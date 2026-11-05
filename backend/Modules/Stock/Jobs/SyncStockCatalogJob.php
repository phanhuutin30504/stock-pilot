<?php

namespace Modules\Stock\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\MarketData\Models\MarketDataSyncLog;
use Modules\Stock\Services\StockCatalogSyncService;
use Throwable;

class SyncStockCatalogJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 60, 120];

    public int $timeout = 120;

    public int $uniqueFor = 300;

    public function uniqueId(): string
    {
        return 'sync-stock-catalog';
    }

    public function handle(StockCatalogSyncService $service): void
    {
        $result = $service->sync();

        if ($result->status !== 'success') {
            Log::warning('SyncStockCatalogJob completed with non-success status', [
                'provider' => $result->provider,
                'status' => $result->status,
            ]);

            $this->fail(new \RuntimeException($result->errorMessage ?? 'Stock catalog sync failed.'));
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SyncStockCatalogJob failed', [
            'exception' => $exception ? $exception::class : null,
            'message' => $exception?->getMessage(),
        ]);

        MarketDataSyncLog::query()->create([
            'provider' => (string) config('stockpilot.provider', 'demo'),
            'sync_type' => 'stock_catalog',
            'status' => 'failed',
            'started_at' => now(),
            'finished_at' => now(),
            'records_count' => 0,
            'error_message' => $exception?->getMessage(),
            'metadata' => ['source' => 'job_failed'],
        ]);
    }
}
