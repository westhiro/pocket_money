<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 業界マスタ
        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->timestamps();
        });

        // 株式・企業マスタ
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('industry_id')->constrained()->onDelete('cascade');
            $table->string('company_name', 100);
            $table->string('stock_symbol', 10)->unique();
            $table->decimal('current_price', 10, 2);
            $table->text('description')->nullable();
            $table->string('logo_url', 255)->nullable();
            $table->timestamps();
        });

        // 株価履歴
        Schema::create('stock_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->decimal('change_percentage', 5, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->index(['stock_id', 'recorded_at']);
        });

        // ユーザー保有株
        Schema::create('user_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('stock_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('average_price', 10, 2);
            $table->decimal('total_invested', 15, 2);
            $table->timestamps();
            
            $table->unique(['user_id', 'stock_id']);
        });

        // イベントマスタ
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description');
            $table->enum('event_type', ['economic', 'industry', 'company', 'natural_disaster', 'political']);
            $table->enum('impact_type', ['positive', 'negative', 'neutral']);
            $table->integer('probability_weight')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // イベント影響設定
        Schema::create('event_impacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->enum('target_type', ['industry', 'stock']);
            $table->unsignedBigInteger('target_id'); // industries.id or stocks.id
            $table->decimal('impact_percentage', 5, 2); // -50.00 to +50.00
            $table->timestamps();
        });

        // イベント発生履歴
        Schema::create('event_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->timestamp('occurred_at');
            $table->integer('duration_minutes')->default(60);
            $table->json('actual_impact')->nullable(); // 実際の株価変動記録
            $table->timestamps();
        });

        // 学習動画
        Schema::create('learning_videos', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('video_url', 500)->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->integer('duration_seconds');
            $table->decimal('coin_reward', 10, 2);
            $table->string('category', 50)->nullable();
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ユーザー学習進捗
        Schema::create('user_learning_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('video_id')->references('id')->on('learning_videos')->onDelete('cascade');
            $table->integer('watch_duration_seconds')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->decimal('coins_earned', 10, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'video_id']);
        });

        // ユーザーコイン履歴
        Schema::create('user_coin_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('transaction_type', ['earn_video', 'buy_stock', 'sell_stock', 'dividend']);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_coin_history');
        Schema::dropIfExists('user_learning_progress');
        Schema::dropIfExists('learning_videos');
        Schema::dropIfExists('event_occurrences');
        Schema::dropIfExists('event_impacts');
        Schema::dropIfExists('events');
        Schema::dropIfExists('user_stocks');
        Schema::dropIfExists('stock_price_history');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('industries');
    }
};