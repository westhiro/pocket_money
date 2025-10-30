<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'content',
        'type',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // お知らせを読んだユーザーとのリレーション
    public function reads()
    {
        return $this->hasMany(NotificationRead::class);
    }

    // 特定のユーザーが既読しているかチェック
    public function isReadByUser($userId)
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    // 公開されているお知らせのみ取得するスコープ
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }
}
