<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon'
    ];

    // リレーション: 株式
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
