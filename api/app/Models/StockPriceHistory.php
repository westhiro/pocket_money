<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockPriceHistory extends Model
{
    protected $table = 'stock_price_history';
    
    protected $fillable = [
        'stock_id',
        'price',
        'change_percentage',
        'recorded_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'change_percentage' => 'decimal:2',
        'recorded_at' => 'datetime'
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
