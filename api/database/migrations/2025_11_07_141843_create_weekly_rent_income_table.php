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
        Schema::create('weekly_rent_income', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('ユーザーID');
            $table->foreignId('user_real_estate_id')->constrained()->onDelete('cascade')->comment('保有不動産ID');
            $table->decimal('base_rent', 10, 2)->comment('基本家賃（万円/月）');
            $table->decimal('vacancy_deduction', 10, 2)->comment('空室控除額（万円）');
            $table->decimal('net_income', 10, 2)->comment('純収入（万円）');
            $table->decimal('vacancy_rate', 5, 2)->comment('適用空室率（%）');
            $table->date('week_start_date')->comment('週開始日');
            $table->timestamps();

            $table->index(['user_id', 'week_start_date']);
            $table->index('user_real_estate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_rent_income');
    }
};
