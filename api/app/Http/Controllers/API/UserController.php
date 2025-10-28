<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserStock;
use App\Models\Stock;

class UserController extends Controller
{
    // ユーザーの保有株式を取得
    public function getStocks(Request $request)
    {
        // リクエストからuser_idを取得（なければ認証ユーザー）
        $userId = $request->input('user_id') ?? $request->header('X-User-Id');

        if (!$userId && Auth::check()) {
            $userId = Auth::id();
        }

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required'
            ], 400);
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        $userStocks = UserStock::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->with(['stock.industry'])
            ->get();
            
        $stocks = $userStocks->map(function($userStock) {
            $stock = $userStock->stock;
            $currentPrice = $stock->current_price;
            $totalValue = $currentPrice * $userStock->quantity;
            $averagePrice = $userStock->average_price;
            $profitLoss = ($currentPrice - $averagePrice) * $userStock->quantity;
            $profitLossPercent = $averagePrice > 0 ? (($currentPrice - $averagePrice) / $averagePrice) * 100 : 0;
            
            return [
                'id' => $stock->id,
                'company_name' => $stock->company_name,
                'stock_symbol' => $stock->stock_symbol,
                'industry' => $stock->industry->name,
                'current_price' => $currentPrice,
                'price_change' => $stock->price_change || 0,
                'quantity' => $userStock->quantity,
                'average_price' => $averagePrice,
                'total_value' => $totalValue,
                'profit_loss' => $profitLoss,
                'profit_loss_percent' => $profitLossPercent,
                'created_at' => $userStock->created_at,
                'updated_at' => $userStock->updated_at,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $stocks
        ]);
    }
    
    // ユーザーの資産情報を取得
    public function getAssets(Request $request)
    {
        // リクエストからuser_idを取得（なければ認証ユーザー）
        $userId = $request->input('user_id') ?? $request->header('X-User-Id');

        if (!$userId && Auth::check()) {
            $userId = Auth::id();
        }

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required'
            ], 400);
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        // 保有株式の合計価値を計算
        $userStocks = UserStock::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->with('stock')
            ->get();
            
        $totalStockValue = $userStocks->sum(function($userStock) {
            return $userStock->stock->current_price * $userStock->quantity;
        });
        
        $totalAssets = $user->current_coins + $totalStockValue;
        
        return response()->json([
            'success' => true,
            'data' => [
                'current_coins' => $user->current_coins,
                'stock_value' => $totalStockValue,
                'total_assets' => $totalAssets,
                'stock_count' => $userStocks->count()
            ]
        ]);
    }

    // ユーザーの資産履歴を取得
    public function getAssetHistory(Request $request)
    {
        // リクエストからuser_idを取得（なければ認証ユーザー）
        $userId = $request->input('user_id') ?? $request->header('X-User-Id');

        if (!$userId && Auth::check()) {
            $userId = Auth::id();
        }

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required'
            ], 400);
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // 期間を取得（デフォルトは1ヶ月）
        $days = $request->input('days', 30);

        // 資産履歴を取得（各日の最後のデータのみ、今日のデータは除外）
        // サブクエリで各日の最後のrecorded_atを取得
        $latestRecords = \DB::table('asset_histories')
            ->select(\DB::raw('DATE(recorded_at) as date'), \DB::raw('MAX(recorded_at) as last_recorded_at'))
            ->where('user_id', $user->id)
            ->where('recorded_at', '>=', now()->subDays($days))
            ->where('recorded_at', '<', now()->startOfDay()) // 今日より前のデータのみ
            ->groupBy(\DB::raw('DATE(recorded_at)'));

        // 各日の最後のレコードを取得
        $histories = \DB::table('asset_histories as ah')
            ->joinSub($latestRecords, 'lr', function($join) {
                $join->on(\DB::raw('DATE(ah.recorded_at)'), '=', 'lr.date')
                     ->on('ah.recorded_at', '=', 'lr.last_recorded_at');
            })
            ->where('ah.user_id', $user->id)
            ->select('ah.*')
            ->orderBy('ah.recorded_at', 'asc')
            ->get();

        $data = $histories->map(function($history) {
            return [
                'date' => date('m/d', strtotime($history->recorded_at)),
                'total_assets' => (float) $history->total_assets,
                'stock_value' => (float) $history->stock_value,
                'coin_balance' => (float) $history->coin_balance,
                'recorded_at' => $history->recorded_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
