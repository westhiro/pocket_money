<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_coins',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'current_coins' => 'decimal:2',
        ];
    }

    // リレーション: 保有株
    public function stocks()
    {
        return $this->hasMany(UserStock::class);
    }

    // リレーション: コイン履歴
    public function coinHistory()
    {
        return $this->hasMany(UserCoinHistory::class)->orderBy('created_at', 'desc');
    }

    // リレーション: 学習進捗
    public function learningProgress()
    {
        return $this->hasMany(UserLearningProgress::class);
    }

    // リレーション: 取引履歴
    public function tradeHistory()
    {
        return $this->hasMany(TradeHistory::class)->orderBy('created_at', 'desc');
    }

    // 総資産計算
    public function getTotalAssets()
    {
        $stockValue = $this->stocks()->with('stock')->get()->sum(function ($userStock) {
            return $userStock->quantity * $userStock->stock->current_price;
        });
        
        return $this->current_coins + $stockValue;
    }
}
