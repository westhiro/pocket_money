<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyRentIncome extends Model
{
    protected $table = 'weekly_rent_income';

    protected $fillable = [
        'user_id',
        'user_real_estate_id',
        'base_rent',
        'vacancy_deduction',
        'net_income',
        'vacancy_rate',
        'week_start_date'
    ];

    protected $casts = [
        'base_rent' => 'decimal:2',
        'vacancy_deduction' => 'decimal:2',
        'net_income' => 'decimal:2',
        'vacancy_rate' => 'decimal:2',
        'week_start_date' => 'date'
    ];

    // リレーション: ユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // リレーション: ユーザー保有不動産
    public function userRealEstate()
    {
        return $this->belongsTo(UserRealEstate::class);
    }
}
