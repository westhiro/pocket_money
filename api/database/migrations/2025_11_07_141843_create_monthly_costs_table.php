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
        Schema::create('monthly_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('ユーザーID');
            $table->foreignId('user_real_estate_id')->constrained()->onDelete('cascade')->comment('保有不動産ID');
            $table->integer('management_fee')->comment('管理費（円）');
            $table->integer('repair_reserve')->comment('修繕積立金（円）');
            $table->integer('total_cost')->comment('合計コスト（円）');
            $table->date('payment_date')->comment('支払日');
            $table->timestamps();

            $table->index(['user_id', 'payment_date']);
            $table->index('user_real_estate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_costs');
    }
};
