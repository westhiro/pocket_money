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
        Schema::create('real_estate_trade_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('ユーザーID');
            $table->foreignId('user_real_estate_id')->nullable()->constrained()->onDelete('set null')->comment('保有不動産ID');
            $table->foreignId('real_estate_id')->nullable()->constrained()->onDelete('set null')->comment('物件ID');
            $table->enum('trade_type', ['buy', 'sell'])->comment('取引種別');
            $table->string('property_name')->comment('物件名');
            $table->decimal('price', 12, 2)->comment('取引価格（万円）');
            $table->decimal('loan_amount', 12, 2)->nullable()->comment('ローン額（万円）');
            $table->decimal('coin_payment', 12, 2)->comment('コイン支払額');
            $table->decimal('coin_change', 12, 2)->comment('コイン変動額');
            $table->decimal('coin_balance_after', 12, 2)->comment('取引後コイン残高');
            $table->timestamps();

            $table->index('user_id');
            $table->index('trade_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_estate_trade_history');
    }
};
