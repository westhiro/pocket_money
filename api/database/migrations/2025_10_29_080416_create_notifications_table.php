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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // お知らせのタイトル
            $table->text('content'); // お知らせの内容
            $table->enum('type', ['info', 'warning', 'important', 'event'])->default('info'); // お知らせの種類
            $table->boolean('is_published')->default(false); // 公開/非公開
            $table->timestamp('published_at')->nullable(); // 公開日時
            $table->timestamps();
        });

        // ユーザーがお知らせを既読したかどうかを記録するテーブル
        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('notification_id')->constrained()->onDelete('cascade');
            $table->timestamp('read_at')->useCurrent();
            $table->timestamps();

            // 同じユーザーが同じお知らせを複数回既読にしないように
            $table->unique(['user_id', 'notification_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_reads');
        Schema::dropIfExists('notifications');
    }
};
