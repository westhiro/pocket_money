<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 取引履歴
        Schema::create('trade_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('stock_id')->constrained()->onDelete('cascade');
            $table->enum('trade_type', ['buy', 'sell']);
            $table->integer('quantity');
            $table->decimal('price_per_share', 10, 2);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('coin_change', 15, 2); // コインの増減
            $table->decimal('coin_balance_after', 15, 2); // 取引後のコイン残高
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['stock_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_history');
    }
};