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
            // 現在のトレンド (upward: 上昇傾向, downward: 減少傾向)
            $table->enum('current_trend', ['upward', 'downward'])->default('upward')->after('current_price');
            // トレンドが最後に変更された時刻
            $table->timestamp('trend_updated_at')->nullable()->after('current_trend');
            // 前回の変動が大きかった場合のフラグ（調整が必要）
            $table->boolean('needs_correction')->default(false)->after('trend_updated_at');
            // 前回の変動率
            $table->decimal('last_change_percentage', 5, 2)->default(0)->after('needs_correction');
            // 緊急イベント中フラグ
            $table->boolean('in_emergency_event')->default(false)->after('last_change_percentage');
            // 緊急イベント終了後の調整が必要かフラグ
            $table->boolean('needs_event_recovery')->default(false)->after('in_emergency_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn([
                'current_trend',
                'trend_updated_at',
                'needs_correction',
                'last_change_percentage',
                'in_emergency_event',
                'needs_event_recovery'
            ]);
        });
    }
};
