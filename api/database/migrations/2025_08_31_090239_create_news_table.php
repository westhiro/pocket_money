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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('ニュースタイトル');
            $table->text('content')->comment('ニュース内容');
            $table->string('news_type')->default('general')->comment('ニュース種類（general/event/market）');
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('cascade')->comment('関連イベントID');
            $table->boolean('is_published')->default(true)->comment('公開フラグ');
            $table->timestamp('published_at')->comment('公開日時');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
