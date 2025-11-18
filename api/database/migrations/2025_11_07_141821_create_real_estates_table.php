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
        Schema::create('real_estates', function (Blueprint $table) {
            $table->id();
            $table->string('property_name')->comment('物件名');
            $table->enum('property_type', ['luxury', 'standard', 'budget'])->comment('物件タイプ');
            $table->decimal('base_price', 10, 2)->comment('基本価格（万円）');
            $table->enum('land_demand', ['rising', 'normal', 'falling'])->comment('土地需要');
            $table->enum('building_age', ['new', 'semi_new', 'old'])->comment('築年数');
            $table->integer('square_meters')->comment('平米数');
            $table->integer('management_fee_per_sqm')->comment('管理費/㎡');
            $table->integer('repair_reserve_per_sqm')->comment('修繕積立金/㎡');
            $table->integer('location_x')->comment('マップX座標');
            $table->integer('location_y')->comment('マップY座標');
            $table->enum('status', ['available', 'sold'])->default('available')->comment('販売状況');
            $table->timestamps();

            $table->index('status');
            $table->index('property_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_estates');
    }
};
