<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterestRateHistory extends Model
{
    protected $table = 'interest_rate_history';

    protected $fillable = [
        'interest_rate',
        'effective_date'
    ];

    protected $casts = [
        'interest_rate' => 'decimal:2',
        'effective_date' => 'date'
    ];

    // 最新の金利を取得
    public static function getCurrentRate()
    {
        return self::orderBy('effective_date', 'desc')->first()?->interest_rate ?? 1.5;
    }

    // 指定日の金利を取得
    public static function getRateByDate($date)
    {
        return self::where('effective_date', '<=', $date)
            ->orderBy('effective_date', 'desc')
            ->first()?->interest_rate ?? 1.5;
    }
}
