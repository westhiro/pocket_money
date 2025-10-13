<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCoinHistory extends Model
{
    protected $table = 'user_coin_history';

    protected $fillable = [
        'user_id',
        'amount',
        'transaction_type',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
