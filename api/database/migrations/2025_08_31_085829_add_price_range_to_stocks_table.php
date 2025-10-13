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
        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('min_price', 10, 2)->default(50.00)->comment('株価の最小値');
            $table->decimal('max_price', 10, 2)->default(200.00)->comment('株価の最大値');
            $table->timestamp('last_updated_at')->nullable()->comment('最後に株価更新された日時');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['min_price', 'max_price', 'last_updated_at']);
        });
    }
};
