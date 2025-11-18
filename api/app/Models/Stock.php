<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'industry_id',
        'company_name',
        'stock_symbol',
        'current_price',
        'description',
        'logo_url',
        'min_price',
        'max_price',
        'last_updated_at',
        'current_trend',
        'trend_updated_at',
        'needs_correction',
        'last_change_percentage',
        'in_emergency_event',
        'needs_event_recovery'
    ];

    protected $casts = [
        'current_price' => 'decimal:2',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'last_updated_at' => 'datetime',
        'trend_updated_at' => 'datetime',
        'needs_correction' => 'boolean',
        'last_change_percentage' => 'decimal:2',
        'in_emergency_event' => 'boolean',
        'needs_event_recovery' => 'boolean'
    ];

    // リレーション: 業界
    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    // リレーション: 株価履歴
    public function priceHistory()
    {
        return $this->hasMany(StockPriceHistory::class)->orderBy('recorded_at', 'desc');
    }

    // リレーション: ユーザー保有株
    public function userStocks()
    {
        return $this->hasMany(UserStock::class);
    }

    // リレーション: 取引履歴
    public function tradeHistory()
    {
        return $this->hasMany(TradeHistory::class);
    }

    // 最新株価取得
    public function getLatestPrice()
    {
        return $this->priceHistory()->first()?->price ?? $this->current_price;
    }

    // 株価変動率計算
    public function getPriceChangePercentage()
    {
        $history = $this->priceHistory()->take(2)->get();
        if ($history->count() < 2) return 0;
        
        $current = $history[0]->price;
        $previous = $history[1]->price;
        
        return (($current - $previous) / $previous) * 100;
    }
}
