<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_data_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50);
            $table->string('sync_type', 50);
            $table->string('symbol')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('finished_at')->nullable();
            $table->unsignedInteger('records_count')->default(0);
            $table->text('error_message')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index(['provider', 'sync_type', 'status']);
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_data_sync_logs');
    }
};
