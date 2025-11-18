<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealEstateTradeHistory extends Model
{
    protected $table = 'real_estate_trade_history';

    protected $fillable = [
        'user_id',
        'user_real_estate_id',
        'real_estate_id',
        'trade_type',
        'property_name',
        'price',
        'loan_amount',
        'coin_payment',
        'coin_change',
        'coin_balance_after'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'loan_amount' => 'decimal:2',
        'coin_payment' => 'decimal:2',
        'coin_change' => 'decimal:2',
        'coin_balance_after' => 'decimal:2'
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

    // リレーション: 不動産物件
    public function realEstate()
    {
        return $this->belongsTo(RealEstate::class);
    }
}
