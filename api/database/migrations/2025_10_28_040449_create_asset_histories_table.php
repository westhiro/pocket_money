<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asset_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('total_assets', 15, 2)->default(0); // 総資産
            $table->decimal('stock_value', 15, 2)->default(0); // 株式評価額
            $table->decimal('coin_balance', 15, 2)->default(0); // コイン残高
            $table->timestamp('recorded_at'); // 記録日時
            $table->timestamps();

            // インデックス
            $table->index(['user_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_histories');
    }
};
