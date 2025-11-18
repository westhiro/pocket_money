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
        Schema::create('user_real_estates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('ユーザーID');
            $table->foreignId('real_estate_id')->nullable()->constrained()->onDelete('set null')->comment('物件ID');
            $table->string('property_name')->comment('物件名');
            $table->string('property_type')->comment('物件タイプ');
            $table->decimal('purchase_price', 12, 2)->comment('購入価格（万円）');
            $table->date('purchase_date')->comment('購入日');
            $table->date('sale_date')->nullable()->comment('売却日');
            $table->decimal('sale_price', 12, 2)->nullable()->comment('売却価格（万円）');
            $table->decimal('loan_balance', 12, 2)->default(0)->comment('ローン残高（万円）');
            $table->decimal('total_loan_amount', 12, 2)->comment('総ローン額（万円）');
            $table->decimal('weekly_principal', 10, 2)->comment('毎週の元本支払額（万円）');
            $table->decimal('current_rent', 10, 2)->comment('現在の家賃（万円/月）');
            $table->decimal('rent_change_rate', 5, 2)->default(0)->comment('家賃変更率（%）');
            $table->decimal('vacancy_rate', 5, 2)->default(0)->comment('空室率（%）');
            $table->decimal('yield_rate', 5, 2)->comment('表面利回り（%）');
            $table->integer('management_cost')->comment('管理費・修繕積立金（円/月）');
            $table->integer('square_meters')->comment('平米数');
            $table->string('land_demand')->comment('購入時の土地需要');
            $table->string('building_age')->comment('購入時の築年数');
            $table->integer('weeks_owned')->default(0)->comment('保有週数');
            $table->boolean('is_sold')->default(false)->comment('売却済みフラグ');
            $table->timestamps();

            $table->index(['user_id', 'is_sold']);
            $table->index('purchase_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_real_estates');
    }
};
