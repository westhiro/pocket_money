<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeHistory extends Model
{
    use HasFactory;

    protected $table = 'trade_history';

    protected $fillable = [
        'user_id',
        'stock_id',
        'trade_type',
        'quantity',
        'price_per_share',
        'total_amount',
        'coin_change',
        'current_coins_after',
        'notes'
    ];

    protected $casts = [
        'price_per_share' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'coin_change' => 'decimal:2',
        'current_coins_after' => 'decimal:2',
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

    // スコープ
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStock($query, $stockId)
    {
        return $query->where('stock_id', $stockId);
    }

    public function scopeBuys($query)
    {
        return $query->where('trade_type', 'buy');
    }

    public function scopeSells($query)
    {
        return $query->where('trade_type', 'sell');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}