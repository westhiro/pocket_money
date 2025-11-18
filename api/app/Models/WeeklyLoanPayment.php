<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyLoanPayment extends Model
{
    protected $table = 'weekly_loan_payment';

    protected $fillable = [
        'user_id',
        'user_real_estate_id',
        'principal_payment',
        'interest_payment',
        'interest_rate',
        'remaining_balance',
        'payment_date'
    ];

    protected $casts = [
        'principal_payment' => 'decimal:2',
        'interest_payment' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
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
