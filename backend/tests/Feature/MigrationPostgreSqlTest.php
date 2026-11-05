<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationPostgreSqlTest extends TestCase
{
    public function test_core_tables_exist_on_pgsql(): void
    {
        $this->assertSame('pgsql', config('database.default'));

        foreach ([
            'industries',
            'stocks',
            'stock_bars',
            'market_indices',
            'market_data_sync_logs',
            'jobs',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }
}
