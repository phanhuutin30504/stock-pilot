<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_indices', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 30)->unique();
            $table->string('name');
            $table->string('exchange')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_indices');
    }
};
