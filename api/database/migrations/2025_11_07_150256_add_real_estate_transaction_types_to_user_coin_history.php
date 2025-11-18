<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ENUMに不動産関連の取引タイプを追加
        DB::statement("ALTER TABLE user_coin_history MODIFY COLUMN transaction_type ENUM('earn_video','buy_stock','sell_stock','dividend','rent_income','loan_payment','property_cost','buy_real_estate','sell_real_estate') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 元のENUMに戻す
        DB::statement("ALTER TABLE user_coin_history MODIFY COLUMN transaction_type ENUM('earn_video','buy_stock','sell_stock','dividend') NOT NULL");
    }
};
