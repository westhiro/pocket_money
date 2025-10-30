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
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subject'); // 件名
            $table->text('message'); // お問い合わせ内容
            $table->enum('status', ['pending', 'in_progress', 'resolved'])->default('pending'); // ステータス
            $table->text('admin_reply')->nullable(); // 運営からの返信
            $table->timestamp('replied_at')->nullable(); // 返信日時
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
