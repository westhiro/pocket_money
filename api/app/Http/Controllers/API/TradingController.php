<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\User;
use App\Models\UserStock;
use App\Models\TradeHistory;
use App\Models\UserCoinHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TradingController extends Controller
{
    // 株式売買処理
    public function trade(Request $request)
    {
        $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'trade_type' => 'required|in:buy,sell',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        $stock = Stock::findOrFail($request->stock_id);
        $quantity = $request->quantity;
        $currentPrice = $stock->current_price;
        $totalAmount = $currentPrice * $quantity;

        DB::beginTransaction();
        try {
            if ($request->trade_type === 'buy') {
                // 購入処理
                $result = $this->processBuy($user, $stock, $quantity, $currentPrice, $totalAmount);
            } else {
                // 売却処理
                $result = $this->processSell($user, $stock, $quantity, $currentPrice, $totalAmount);
            }

            if (!$result['success']) {
                DB::rollback();
                return response()->json($result, 400);
            }

            DB::commit();
            return response()->json($result);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => '取引処理中にエラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processBuy($user, $stock, $quantity, $currentPrice, $totalAmount)
    {
        // コイン残高チェック
        if ($user->current_coins < $totalAmount) {
            return [
                'success' => false,
                'message' => 'コインが不足しています。現在の残高: ' . number_format($user->current_coins) . 'コイン'
            ];
        }

        // ユーザー保有株の更新または作成
        $userStock = UserStock::where([
            'user_id' => $user->id,
            'stock_id' => $stock->id
        ])->first();

        if ($userStock) {
            // 既存保有株の更新（平均価格計算）
            $totalShares = $userStock->quantity + $quantity;
            $totalInvested = $userStock->total_invested + $totalAmount;
            $newAveragePrice = $totalInvested / $totalShares;

            $userStock->update([
                'quantity' => $totalShares,
                'average_price' => $newAveragePrice,
                'total_invested' => $totalInvested
            ]);
        } else {
            // 新規保有株作成
            UserStock::create([
                'user_id' => $user->id,
                'stock_id' => $stock->id,
                'quantity' => $quantity,
                'average_price' => $currentPrice,
                'total_invested' => $totalAmount
            ]);
        }

        // コイン残高更新
        $newCoinBalance = $user->current_coins - $totalAmount;
        $user->update(['current_coins' => $newCoinBalance]);

        // 取引履歴記録
        TradeHistory::create([
            'user_id' => $user->id,
            'stock_id' => $stock->id,
            'trade_type' => 'buy',
            'quantity' => $quantity,
            'price_per_share' => $currentPrice,
            'total_amount' => $totalAmount,
            'coin_change' => -$totalAmount,
            'current_coins_after' => $newCoinBalance
        ]);

        // コイン履歴記録
        UserCoinHistory::create([
            'user_id' => $user->id,
            'amount' => -$totalAmount,
            'transaction_type' => 'buy_stock',
            'description' => $stock->company_name . ' を' . $quantity . '株購入'
        ]);

        return [
            'success' => true,
            'message' => $stock->company_name . ' を' . $quantity . '株購入しました',
            'data' => [
                'trade_type' => 'buy',
                'stock' => $stock->company_name,
                'quantity' => $quantity,
                'price_per_share' => $currentPrice,
                'total_amount' => $totalAmount,
                'remaining_coins' => $newCoinBalance
            ]
        ];
    }

    private function processSell($user, $stock, $quantity, $currentPrice, $totalAmount)
    {
        // 保有株確認
        $userStock = UserStock::where([
            'user_id' => $user->id,
            'stock_id' => $stock->id
        ])->first();

        if (!$userStock || $userStock->quantity < $quantity) {
            return [
                'success' => false,
                'message' => '保有株数が不足しています。現在の保有数: ' . ($userStock ? $userStock->quantity : 0) . '株'
            ];
        }

        // 保有株数更新
        if ($userStock->quantity == $quantity) {
            // 全売却
            $userStock->delete();
        } else {
            // 一部売却
            $newQuantity = $userStock->quantity - $quantity;
            $soldRatio = $quantity / $userStock->quantity;
            $newTotalInvested = $userStock->total_invested * (1 - $soldRatio);
            
            $userStock->update([
                'quantity' => $newQuantity,
                'total_invested' => $newTotalInvested
            ]);
        }

        // コイン残高更新
        $newCoinBalance = $user->current_coins + $totalAmount;
        $user->update(['current_coins' => $newCoinBalance]);

        // 取引履歴記録
        TradeHistory::create([
            'user_id' => $user->id,
            'stock_id' => $stock->id,
            'trade_type' => 'sell',
            'quantity' => $quantity,
            'price_per_share' => $currentPrice,
            'total_amount' => $totalAmount,
            'coin_change' => $totalAmount,
            'current_coins_after' => $newCoinBalance
        ]);

        // コイン履歴記録
        UserCoinHistory::create([
            'user_id' => $user->id,
            'amount' => $totalAmount,
            'transaction_type' => 'sell_stock',
            'description' => $stock->company_name . ' を' . $quantity . '株売却'
        ]);

        return [
            'success' => true,
            'message' => $stock->company_name . ' を' . $quantity . '株売却しました',
            'data' => [
                'trade_type' => 'sell',
                'stock' => $stock->company_name,
                'quantity' => $quantity,
                'price_per_share' => $currentPrice,
                'total_amount' => $totalAmount,
                'remaining_coins' => $newCoinBalance
            ]
        ];
    }

    // ユーザーポートフォリオ取得
    public function portfolio()
    {
        $user = Auth::user();
        $userStocks = $user->stocks()->with('stock.industry')->get();

        $portfolio = $userStocks->map(function ($userStock) {
            $currentValue = $userStock->quantity * $userStock->stock->current_price;
            $profitLoss = $currentValue - $userStock->total_invested;
            $profitLossPercent = ($profitLoss / $userStock->total_invested) * 100;

            return [
                'id' => $userStock->id,
                'stock_id' => $userStock->stock->id,
                'company_name' => $userStock->stock->company_name,
                'stock_symbol' => $userStock->stock->stock_symbol,
                'industry' => $userStock->stock->industry->name,
                'quantity' => $userStock->quantity,
                'average_price' => $userStock->average_price,
                'current_price' => $userStock->stock->current_price,
                'total_invested' => $userStock->total_invested,
                'current_value' => $currentValue,
                'profit_loss' => $profitLoss,
                'profit_loss_percent' => $profitLossPercent
            ];
        });

        $totalInvested = $portfolio->sum('total_invested');
        $totalCurrentValue = $portfolio->sum('current_value');
        $totalProfitLoss = $totalCurrentValue - $totalInvested;
        $totalProfitLossPercent = $totalInvested > 0 ? ($totalProfitLoss / $totalInvested) * 100 : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'current_coins' => $user->current_coins,
                'total_invested' => $totalInvested,
                'total_current_value' => $totalCurrentValue,
                'total_profit_loss' => $totalProfitLoss,
                'total_profit_loss_percent' => $totalProfitLossPercent,
                'total_assets' => $user->current_coins + $totalCurrentValue,
                'holdings' => $portfolio
            ]
        ]);
    }

    // 取引履歴取得
    public function history(Request $request)
    {
        $user = Auth::user();
        $limit = $request->get('limit', 50);
        
        $history = $user->tradeHistory()
            ->with('stock')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($trade) {
                return [
                    'id' => $trade->id,
                    'company_name' => $trade->stock->company_name,
                    'stock_symbol' => $trade->stock->stock_symbol,
                    'trade_type' => $trade->trade_type,
                    'quantity' => $trade->quantity,
                    'price_per_share' => $trade->price_per_share,
                    'total_amount' => $trade->total_amount,
                    'coin_change' => $trade->coin_change,
                    'trade_date' => $trade->created_at->format('Y-m-d H:i:s')
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    // テスト用取引処理（認証不要）
    public function tradeTest(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'stock_id' => 'required|exists:stocks,id',
            'trade_type' => 'required|in:buy,sell',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = User::findOrFail($request->user_id);
        $stock = Stock::findOrFail($request->stock_id);
        $quantity = $request->quantity;
        $currentPrice = $stock->current_price;
        $totalAmount = $currentPrice * $quantity;

        DB::beginTransaction();
        try {
            if ($request->trade_type === 'buy') {
                $result = $this->processBuy($user, $stock, $quantity, $currentPrice, $totalAmount);
            } else {
                $result = $this->processSell($user, $stock, $quantity, $currentPrice, $totalAmount);
            }

            if (!$result['success']) {
                DB::rollback();
                return response()->json($result, 400);
            }

            DB::commit();
            return response()->json($result);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => '取引処理中にエラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    // テスト用ポートフォリオ取得（認証不要）
    public function portfolioTest($userId)
    {
        $user = User::findOrFail($userId);
        $userStocks = $user->stocks()->with('stock.industry')->get();

        $portfolio = $userStocks->map(function ($userStock) {
            $currentValue = $userStock->quantity * $userStock->stock->current_price;
            $profitLoss = $currentValue - $userStock->total_invested;
            $profitLossPercent = $userStock->total_invested > 0 ? ($profitLoss / $userStock->total_invested) * 100 : 0;

            return [
                'id' => $userStock->id,
                'stock_id' => $userStock->stock->id,
                'company_name' => $userStock->stock->company_name,
                'stock_symbol' => $userStock->stock->stock_symbol,
                'industry' => $userStock->stock->industry->name,
                'quantity' => $userStock->quantity,
                'average_price' => $userStock->average_price,
                'current_price' => $userStock->stock->current_price,
                'total_invested' => $userStock->total_invested,
                'current_value' => $currentValue,
                'profit_loss' => $profitLoss,
                'profit_loss_percent' => $profitLossPercent
            ];
        });

        $totalInvested = $portfolio->sum('total_invested');
        $totalCurrentValue = $portfolio->sum('current_value');
        $totalProfitLoss = $totalCurrentValue - $totalInvested;
        $totalProfitLossPercent = $totalInvested > 0 ? ($totalProfitLoss / $totalInvested) * 100 : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'current_coins' => $user->current_coins,
                'total_invested' => $totalInvested,
                'total_current_value' => $totalCurrentValue,
                'total_profit_loss' => $totalProfitLoss,
                'total_profit_loss_percent' => $totalProfitLossPercent,
                'total_assets' => $user->current_coins + $totalCurrentValue,
                'holdings' => $portfolio
            ]
        ]);
    }
}