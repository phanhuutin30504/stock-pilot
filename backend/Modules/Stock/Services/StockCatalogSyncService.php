<?php

namespace Modules\Stock\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\MarketData\Models\MarketDataSyncLog;
use Modules\Stock\Contracts\StockCatalogProviderInterface;
use Modules\Stock\DTOs\StockCatalogSyncResult;
use Modules\Stock\DTOs\StockData;
use Modules\Stock\Models\Industry;
use Modules\Stock\Models\Stock;
use Throwable;

class StockCatalogSyncService
{
    public function __construct(
        private readonly StockCatalogProviderInterface $provider,
    ) {
    }

    public function sync(): StockCatalogSyncResult
    {
        $providerName = $this->provider->providerName();
        $startedAt = now();

        $log = MarketDataSyncLog::query()->create([
            'provider' => $providerName,
            'sync_type' => 'stock_catalog',
            'status' => 'pending',
            'started_at' => $startedAt,
            'records_count' => 0,
            'metadata' => ['phase' => 'fetch'],
        ]);

        try {
            // Fetch/validate outside a long DB transaction so network I/O never holds locks.
            $stocks = [];
            foreach ($this->provider->fetchStocks() as $stockData) {
                if (! $stockData instanceof StockData) {
                    throw new \InvalidArgumentException('Provider must yield StockData instances.');
                }
                $stocks[] = $stockData;
            }

            $batchSize = max(1, (int) config('stockpilot.sync.batch_size', 50));
            $inserted = 0;
            $updated = 0;
            $unchanged = 0;

            $industryIdsByCode = Industry::query()
                ->whereIn('code', array_values(array_filter(array_map(
                    static fn (StockData $s) => $s->normalizedIndustryCode(),
                    $stocks
                ))))
                ->pluck('id', 'code')
                ->all();

            foreach (array_chunk($stocks, $batchSize) as $chunk) {
                DB::transaction(function () use ($chunk, $industryIdsByCode, &$inserted, &$updated, &$unchanged) {
                    foreach ($chunk as $stockData) {
                        $attributes = $stockData->toPersistenceArray();
                        $industryCode = $stockData->normalizedIndustryCode();
                        $attributes['industry_id'] = $industryCode !== null
                            ? ($industryIdsByCode[$industryCode] ?? null)
                            : null;

                        $existing = Stock::query()->where('symbol', $attributes['symbol'])->first();

                        if ($existing === null) {
                            Stock::query()->create($attributes);
                            $inserted++;
                            continue;
                        }

                        $dirty = false;
                        foreach (['name', 'exchange', 'company_name', 'industry_id'] as $field) {
                            if ($existing->{$field} != $attributes[$field]) {
                                $dirty = true;
                                break;
                            }
                        }

                        if ($dirty) {
                            $existing->fill($attributes);
                            $existing->save();
                            $updated++;
                        } else {
                            $unchanged++;
                        }
                    }
                });
            }

            $result = new StockCatalogSyncResult(
                provider: $providerName,
                fetched: count($stocks),
                inserted: $inserted,
                updated: $updated,
                unchanged: $unchanged,
                status: 'success',
            );

            $log->update([
                'status' => 'success',
                'finished_at' => now(),
                'records_count' => $result->processed(),
                'error_message' => null,
                'metadata' => [
                    'fetched' => $result->fetched,
                    'inserted' => $result->inserted,
                    'updated' => $result->updated,
                    'unchanged' => $result->unchanged,
                ],
            ]);

            return $result;
        } catch (Throwable $e) {
            Log::error('Stock catalog sync failed', [
                'provider' => $providerName,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            $log->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $e->getMessage(),
                'metadata' => ['exception' => $e::class],
            ]);

            return new StockCatalogSyncResult(
                provider: $providerName,
                fetched: 0,
                inserted: 0,
                updated: 0,
                unchanged: 0,
                status: 'failed',
                errorMessage: $e->getMessage(),
            );
        }
    }
}
