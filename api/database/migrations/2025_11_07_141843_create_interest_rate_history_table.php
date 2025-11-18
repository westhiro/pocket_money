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
        Schema::create('interest_rate_history', function (Blueprint $table) {
            $table->id();
            $table->decimal('interest_rate', 5, 2)->comment('金利率（%）');
            $table->date('effective_date')->comment('適用開始日');
            $table->timestamps();

            $table->index('effective_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interest_rate_history');
    }
};
