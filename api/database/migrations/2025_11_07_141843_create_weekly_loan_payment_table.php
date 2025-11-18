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
        Schema::create('weekly_loan_payment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('ユーザーID');
            $table->foreignId('user_real_estate_id')->constrained()->onDelete('cascade')->comment('保有不動産ID');
            $table->decimal('principal_payment', 10, 2)->comment('元本支払額（万円）');
            $table->decimal('interest_payment', 10, 2)->comment('金利支払額（万円）');
            $table->decimal('interest_rate', 5, 2)->comment('適用金利率（%）');
            $table->decimal('remaining_balance', 12, 2)->comment('支払後残高（万円）');
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
        Schema::dropIfExists('weekly_loan_payment');
    }
};
