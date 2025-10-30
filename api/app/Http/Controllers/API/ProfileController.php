<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    // プロフィール取得
    public function show(Request $request)
    {
        $userId = $request->header('X-User-Id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'ユーザーIDが必要です'
            ], 400);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'ユーザーが見つかりません'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'current_coins' => $user->current_coins,
                'total_earned_coins' => $user->total_earned_coins,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    // プロフィール更新
    public function update(Request $request)
    {
        $userId = $request->header('X-User-Id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'ユーザーIDが必要です'
            ], 400);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'ユーザーが見つかりません'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'current_password' => 'required_with:new_password',
            'new_password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        // 名前の更新
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        // メールアドレスの更新
        if ($request->has('email')) {
            $user->email = $request->email;
        }

        // パスワードの更新
        if ($request->has('new_password')) {
            // 現在のパスワードをチェック
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => '現在のパスワードが正しくありません'
                ], 400);
            }

            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'プロフィールを更新しました',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'current_coins' => $user->current_coins,
                'total_earned_coins' => $user->total_earned_coins,
            ]
        ]);
    }
}
