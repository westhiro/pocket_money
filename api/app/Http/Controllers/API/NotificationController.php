<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationRead;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // お知らせ一覧取得（ユーザー向け）
    public function index(Request $request)
    {
        $userId = $request->header('X-User-Id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'ユーザーIDが必要です'
            ], 400);
        }

        // 公開されているお知らせを新しい順に取得
        $notifications = Notification::published()
            ->orderBy('published_at', 'desc')
            ->get()
            ->map(function ($notification) use ($userId) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'content' => $notification->content,
                    'type' => $notification->type,
                    'published_at' => $notification->published_at->format('Y-m-d H:i:s'),
                    'is_read' => $notification->isReadByUser($userId),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    // 個別お知らせ取得
    public function show($id, Request $request)
    {
        $userId = $request->header('X-User-Id');

        $notification = Notification::published()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'お知らせが見つかりません'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $notification->id,
                'title' => $notification->title,
                'content' => $notification->content,
                'type' => $notification->type,
                'published_at' => $notification->published_at->format('Y-m-d H:i:s'),
                'is_read' => $userId ? $notification->isReadByUser($userId) : false,
            ]
        ]);
    }

    // お知らせを既読にする
    public function markAsRead($id, Request $request)
    {
        $userId = $request->header('X-User-Id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'ユーザーIDが必要です'
            ], 400);
        }

        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'お知らせが見つかりません'
            ], 404);
        }

        // 既に既読の場合はスキップ
        if ($notification->isReadByUser($userId)) {
            return response()->json([
                'success' => true,
                'message' => '既に既読済みです'
            ]);
        }

        // 既読レコードを作成
        NotificationRead::create([
            'user_id' => $userId,
            'notification_id' => $id,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'お知らせを既読にしました'
        ]);
    }

    // 未読件数を取得
    public function getUnreadCount(Request $request)
    {
        $userId = $request->header('X-User-Id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'ユーザーIDが必要です'
            ], 400);
        }

        $unreadCount = Notification::published()
            ->whereDoesntHave('reads', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $unreadCount
            ]
        ]);
    }
}
