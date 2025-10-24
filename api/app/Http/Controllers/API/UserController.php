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
        
        $totalAssets = $user->coin_balance + $totalStockValue;
        
        return response()->json([
            'success' => true,
            'data' => [
                'coin_balance' => $user->coin_balance,
                'stock_value' => $totalStockValue,
                'total_assets' => $totalAssets,
                'stock_count' => $userStocks->count()
            ]
        ]);
    }
}
