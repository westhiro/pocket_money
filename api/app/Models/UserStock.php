<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStock extends Model
{
    protected $table = 'user_stocks';

    protected $fillable = [
        'user_id',
        'stock_id',
        'quantity',
        'average_price',
        'total_invested'
    ];

    protected $casts = [
        'average_price' => 'decimal:2',
        'total_invested' => 'decimal:2'
    ];

    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
