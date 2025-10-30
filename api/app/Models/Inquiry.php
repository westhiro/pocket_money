<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'status',
        'admin_reply',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    // ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 未対応のお問い合わせのみ取得するスコープ
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // 対応中のお問い合わせのみ取得するスコープ
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    // 解決済みのお問い合わせのみ取得するスコープ
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}
