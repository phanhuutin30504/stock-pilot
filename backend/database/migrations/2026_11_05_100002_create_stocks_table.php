<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 20)->unique();
            $table->string('name');
            $table->string('exchange', 20)->index();
            $table->foreignId('industry_id')
                ->nullable()
                ->constrained('industries')
                ->nullOnDelete();
            $table->string('company_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index(['exchange', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
