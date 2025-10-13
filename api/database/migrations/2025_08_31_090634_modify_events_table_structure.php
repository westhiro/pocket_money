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
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'title')) {
                $table->string('title')->comment('イベントタイトル');
            }
            if (!Schema::hasColumn('events', 'description')) {
                $table->text('description')->comment('イベント詳細');
            }
            if (!Schema::hasColumn('events', 'event_type')) {
                $table->string('event_type')->comment('イベント種類（positive/negative）');
            }
            if (!Schema::hasColumn('events', 'industry_id')) {
                $table->foreignId('industry_id')->nullable()->constrained()->onDelete('cascade')->comment('影響を受ける業界ID');
            }
            if (!Schema::hasColumn('events', 'impact_percentage')) {
                $table->decimal('impact_percentage', 5, 2)->comment('株価への影響率（%）');
            }
            if (!Schema::hasColumn('events', 'is_active')) {
                $table->boolean('is_active')->default(true)->comment('イベントが有効かどうか');
            }
            if (!Schema::hasColumn('events', 'occurred_at')) {
                $table->timestamp('occurred_at')->nullable()->comment('イベント発生日時');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
