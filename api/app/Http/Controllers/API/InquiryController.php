<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InquiryController extends Controller
{
    // お問い合わせ一覧取得（ユーザー向け）
    public function index(Request $request)
    {
        $userId = $request->header('X-User-Id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'ユーザーIDが必要です'
            ], 400);
        }

        // ユーザーのお問い合わせを新しい順に取得
        $inquiries = Inquiry::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($inquiry) {
                return [
                    'id' => $inquiry->id,
                    'subject' => $inquiry->subject,
                    'message' => $inquiry->message,
                    'status' => $inquiry->status,
                    'admin_reply' => $inquiry->admin_reply,
                    'replied_at' => $inquiry->replied_at ? $inquiry->replied_at->format('Y-m-d H:i:s') : null,
                    'created_at' => $inquiry->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $inquiries
        ]);
    }

    // 個別お問い合わせ取得
    public function show($id, Request $request)
    {
        $userId = $request->header('X-User-Id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'ユーザーIDが必要です'
            ], 400);
        }

        $inquiry = Inquiry::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$inquiry) {
            return response()->json([
                'success' => false,
                'message' => 'お問い合わせが見つかりません'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $inquiry->id,
                'subject' => $inquiry->subject,
                'message' => $inquiry->message,
                'status' => $inquiry->status,
                'admin_reply' => $inquiry->admin_reply,
                'replied_at' => $inquiry->replied_at ? $inquiry->replied_at->format('Y-m-d H:i:s') : null,
                'created_at' => $inquiry->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    // お問い合わせ作成
    public function store(Request $request)
    {
        $userId = $request->header('X-User-Id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'ユーザーIDが必要です'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $inquiry = Inquiry::create([
            'user_id' => $userId,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'お問い合わせを送信しました',
            'data' => [
                'id' => $inquiry->id,
                'subject' => $inquiry->subject,
                'message' => $inquiry->message,
                'status' => $inquiry->status,
                'created_at' => $inquiry->created_at->format('Y-m-d H:i:s'),
            ]
        ], 201);
    }
}
