<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealEstate extends Model
{
    protected $fillable = [
        'property_name',
        'property_type',
        'base_price',
        'land_demand',
        'building_age',
        'square_meters',
        'management_fee_per_sqm',
        'repair_reserve_per_sqm',
        'location_x',
        'location_y',
        'status'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'square_meters' => 'integer',
        'management_fee_per_sqm' => 'integer',
        'repair_reserve_per_sqm' => 'integer',
        'location_x' => 'integer',
        'location_y' => 'integer'
    ];

    // リレーション: ユーザー保有不動産
    public function userRealEstates()
    {
        return $this->hasMany(UserRealEstate::class);
    }

    // リレーション: 取引履歴
    public function tradeHistory()
    {
        return $this->hasMany(RealEstateTradeHistory::class);
    }

    // 購入価格を計算
    public function calculatePurchasePrice()
    {
        $landDemandRate = match($this->land_demand) {
            'rising' => 2.0,
            'normal' => 1.5,
            'falling' => 1.0,
            default => 1.5
        };

        $buildingAgeRate = match($this->building_age) {
            'new' => 1.0,
            'semi_new' => 0.8,
            'old' => 0.6,
            default => 1.0
        };

        return $this->base_price * $landDemandRate * $buildingAgeRate;
    }

    // 月額ランニングコストを計算
    public function calculateMonthlyCost()
    {
        return ($this->management_fee_per_sqm + $this->repair_reserve_per_sqm) * $this->square_meters;
    }

    // 表面利回りを計算（土地需要と評価による）
    public function calculateYieldRate($evaluation = 'normal')
    {
        $yieldRanges = [
            'rising' => ['good' => [4.0, 5.0], 'normal' => [3.0, 3.9], 'bad' => [2.0, 2.9]],
            'normal' => ['good' => [5.0, 6.5], 'normal' => [4.0, 4.9], 'bad' => [3.0, 3.9]],
            'falling' => ['good' => [8.0, 10.0], 'normal' => [6.0, 7.9], 'bad' => [5.0, 5.9]]
        ];

        $range = $yieldRanges[$this->land_demand][$evaluation] ?? [4.0, 5.0];
        return round(rand($range[0] * 100, $range[1] * 100) / 100, 2);
    }
}
