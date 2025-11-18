<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyCost extends Model
{
    protected $fillable = [
        'user_id',
        'user_real_estate_id',
        'management_fee',
        'repair_reserve',
        'total_cost',
        'payment_date'
    ];

    protected $casts = [
        'management_fee' => 'integer',
        'repair_reserve' => 'integer',
        'total_cost' => 'integer',
        'payment_date' => 'date'
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
