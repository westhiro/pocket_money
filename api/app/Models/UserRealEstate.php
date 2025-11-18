<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRealEstate extends Model
{
    protected $fillable = [
        'user_id',
        'real_estate_id',
        'property_name',
        'property_type',
        'purchase_price',
        'purchase_date',
        'sale_date',
        'sale_price',
        'loan_balance',
        'total_loan_amount',
        'weekly_principal',
        'current_rent',
        'rent_change_rate',
        'vacancy_rate',
        'yield_rate',
        'management_cost',
        'square_meters',
        'land_demand',
        'building_age',
        'weeks_owned',
        'is_sold'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'purchase_date' => 'date',
        'sale_date' => 'date',
        'sale_price' => 'decimal:2',
        'loan_balance' => 'decimal:2',
        'total_loan_amount' => 'decimal:2',
        'weekly_principal' => 'decimal:2',
        'current_rent' => 'decimal:2',
        'rent_change_rate' => 'decimal:2',
        'vacancy_rate' => 'decimal:2',
        'yield_rate' => 'decimal:2',
        'management_cost' => 'integer',
        'square_meters' => 'integer',
        'weeks_owned' => 'integer',
        'is_sold' => 'boolean'
    ];

    // リレーション: ユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // リレーション: 不動産物件
    public function realEstate()
    {
        return $this->belongsTo(RealEstate::class);
    }

    // リレーション: 家賃収入履歴
    public function rentIncomes()
    {
        return $this->hasMany(WeeklyRentIncome::class);
    }

    // リレーション: ローン支払履歴
    public function loanPayments()
    {
        return $this->hasMany(WeeklyLoanPayment::class);
    }

    // リレーション: 月次コスト
    public function monthlyCosts()
    {
        return $this->hasMany(MonthlyCost::class);
    }

    // リレーション: 取引履歴
    public function tradeHistory()
    {
        return $this->hasMany(RealEstateTradeHistory::class);
    }

    // 空室率を計算
    public function calculateVacancyRate()
    {
        $landDemandRate = match($this->land_demand) {
            'rising' => 0,
            'normal' => 5,
            'falling' => 10,
            default => 5
        };

        $buildingAgeRate = match($this->building_age) {
            'new' => 0,
            'semi_new' => 5,
            'old' => 10,
            default => 5
        };

        return $landDemandRate + $buildingAgeRate + $this->rent_change_rate;
    }

    // 週次の純家賃収入を計算
    public function calculateWeeklyNetRent()
    {
        $monthlyRent = $this->current_rent * (1 - $this->vacancy_rate / 100);
        return round($monthlyRent / 4, 2); // 月を4週で割る
    }

    // 売却価格を計算
    public function calculateSalePrice()
    {
        $landDemandDepreciation = match($this->land_demand) {
            'rising' => 0,
            'normal' => 1,
            'falling' => 2,
            default => 1
        };

        // 購入日から今日までの日数（絶対値）
        $daysOwned = abs(now()->diffInDays($this->purchase_date, false));
        $holdingDepreciation = $daysOwned * 0.005;

        $totalDepreciation = $landDemandDepreciation + $holdingDepreciation;
        return $this->purchase_price * (1 - $totalDepreciation / 100);
    }

    // ローン残高を更新
    public function updateLoanBalance($payment)
    {
        $this->loan_balance = max(0, $this->loan_balance - $payment);
        $this->save();
    }
}
