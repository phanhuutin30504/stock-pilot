<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_bars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->cascadeOnDelete();
            $table->string('timeframe', 10);
            $table->date('trading_date');
            $table->timestampTz('started_at');
            $table->decimal('open', 18, 4);
            $table->decimal('high', 18, 4);
            $table->decimal('low', 18, 4);
            $table->decimal('close', 18, 4);
            $table->unsignedBigInteger('volume')->default(0);
            $table->boolean('is_adjusted')->default(false);
            $table->string('source', 50);
            $table->timestamps();

            $table->unique(
                ['stock_id', 'timeframe', 'started_at', 'is_adjusted'],
                'stock_bars_unique_bar'
            );
            $table->index(['stock_id', 'timeframe', 'started_at']);
            $table->index(['timeframe', 'trading_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_bars');
    }
};
