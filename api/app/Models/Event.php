<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'event_type',
        'industry_id',
        'impact_percentage',
        'is_active',
        'occurred_at'
    ];

    protected $casts = [
        'impact_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'occurred_at' => 'datetime'
    ];

    // 業界との関連
    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    // ニュースとの関連
    public function news()
    {
        return $this->hasMany(News::class);
    }
}
