<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RealEstate;
use App\Models\UserRealEstate;
use App\Models\RealEstateTradeHistory;
use App\Models\InterestRateHistory;
use App\Models\User;
use App\Models\UserCoinHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RealEstateTradingController extends Controller
{
    /**
     * 不動産購入処理
     * POST /api/real-estate-trading/buy
     */
    public function buy(Request $request)
    {
        $request->validate([
            'real_estate_id' => 'required|exists:real_estates,id',
            'user_id' => 'required|exists:users,id',
            'down_payment' => 'nullable|numeric|min:0',
            'loan_period_weeks' => 'nullable|integer|min:1',
            'monthly_rent' => 'nullable|numeric|min:0'
        ]);

        $user = User::findOrFail($request->user_id);
        $realEstate = RealEstate::findOrFail($request->real_estate_id);

        // 物件が販売可能か確認
        if ($realEstate->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'この物件は現在購入できません'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $result = $this->processBuy($user, $realEstate, $request);

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
                'message' => '購入処理中にエラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 購入処理の実装
     */
    private function processBuy($user, $realEstate, $request)
    {
        // 購入価格を計算
        $purchasePrice = $realEstate->calculatePurchasePrice();

        // 頭金を取得（指定がなければ0円）
        $downPayment = $request->input('down_payment', 0);

        // 頭金が購入価格を超えないようにチェック
        if ($downPayment > $purchasePrice) {
            return [
                'success' => false,
                'message' => '頭金が購入価格を超えています'
            ];
        }

        // コイン残高チェック（頭金分のコインが必要）
        if ($user->current_coins < $downPayment) {
            return [
                'success' => false,
                'message' => 'コインが不足しています'
            ];
        }

        $coinPayment = $downPayment;

        // ローン情報を計算
        $loanAmount = $purchasePrice - $downPayment;
        $loanPeriodWeeks = $request->input('loan_period_weeks', 480); // デフォルト40年（480週）
        $weeklyPrincipal = $loanPeriodWeeks > 0 ? $loanAmount / $loanPeriodWeeks : 0;

        // 家賃を取得（指定がなければ利回りベースで計算）
        $monthlyRent = $request->input('monthly_rent');
        if ($monthlyRent === null || $monthlyRent <= 0) {
            // 利回りと家賃を計算（デフォルトは'normal'評価）
            $yieldRate = $realEstate->calculateYieldRate('normal');
            $monthlyRent = ($yieldRate * $purchasePrice) / (100 * 12);
        }

        // 空室率を計算
        $landDemandRate = match($realEstate->land_demand) {
            'rising' => 0,
            'normal' => 5,
            'falling' => 10,
            default => 5
        };

        $buildingAgeRate = match($realEstate->building_age) {
            'new' => 0,
            'semi_new' => 5,
            'old' => 10,
            default => 5
        };

        $vacancyRate = $landDemandRate + $buildingAgeRate; // 初期家賃変更率は0

        // 管理費・修繕積立金を計算
        $managementCost = $realEstate->calculateMonthlyCost();

        // ユーザー保有不動産を作成
        $userRealEstate = UserRealEstate::create([
            'user_id' => $user->id,
            'real_estate_id' => $realEstate->id,
            'property_name' => $realEstate->property_name,
            'property_type' => $realEstate->property_type,
            'purchase_price' => $purchasePrice,
            'purchase_date' => now()->toDateString(),
            'loan_balance' => $loanAmount,
            'total_loan_amount' => $loanAmount,
            'weekly_principal' => $weeklyPrincipal,
            'current_rent' => $monthlyRent,
            'rent_change_rate' => 0,
            'vacancy_rate' => $vacancyRate,
            'yield_rate' => $yieldRate,
            'management_cost' => $managementCost,
            'square_meters' => $realEstate->square_meters,
            'land_demand' => $realEstate->land_demand,
            'building_age' => $realEstate->building_age,
            'weeks_owned' => 0,
            'is_sold' => false
        ]);

        // コイン残高更新（頭金分を引く）
        $newCoinBalance = $user->current_coins - $coinPayment;
        $user->update(['current_coins' => $newCoinBalance]);

        // 取引履歴を記録
        RealEstateTradeHistory::create([
            'user_id' => $user->id,
            'user_real_estate_id' => $userRealEstate->id,
            'real_estate_id' => $realEstate->id,
            'trade_type' => 'buy',
            'property_name' => $realEstate->property_name,
            'price' => $purchasePrice,
            'loan_amount' => $loanAmount,
            'coin_payment' => $coinPayment,
            'coin_change' => -$coinPayment,
            'coin_balance_after' => $newCoinBalance
        ]);

        // コイン履歴記録（頭金がある場合のみ）
        if ($coinPayment > 0) {
            UserCoinHistory::create([
                'user_id' => $user->id,
                'amount' => -$coinPayment,
                'transaction_type' => 'buy_real_estate',
                'description' => $realEstate->property_name . ' を購入（頭金: ' . round($coinPayment, 2) . '万円）'
            ]);
        }

        return [
            'success' => true,
            'message' => $realEstate->property_name . ' を購入しました',
            'data' => [
                'user_real_estate_id' => $userRealEstate->id,
                'property_name' => $realEstate->property_name,
                'purchase_price' => $purchasePrice,
                'loan_amount' => $loanAmount,
                'weekly_principal' => round($weeklyPrincipal, 2),
                'monthly_rent' => round($monthlyRent, 2),
                'vacancy_rate' => $vacancyRate,
                'management_cost' => $managementCost,
                'remaining_coins' => $newCoinBalance
            ]
        ];
    }

    /**
     * 不動産売却処理
     * POST /api/real-estate-trading/sell
     */
    public function sell(Request $request)
    {
        $request->validate([
            'user_real_estate_id' => 'required|exists:user_real_estates,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);
        $userRealEstate = UserRealEstate::findOrFail($request->user_real_estate_id);

        // 保有者確認
        if ($userRealEstate->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'この物件を売却する権限がありません'
            ], 403);
        }

        // 売却済み確認
        if ($userRealEstate->is_sold) {
            return response()->json([
                'success' => false,
                'message' => 'この物件は既に売却済みです'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $result = $this->processSell($user, $userRealEstate);

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
                'message' => '売却処理中にエラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 売却処理の実装
     */
    private function processSell($user, $userRealEstate)
    {
        // 売却価格を計算
        $salePrice = $userRealEstate->calculateSalePrice();

        // ローン残高を確認
        $loanBalance = $userRealEstate->loan_balance;

        // 売却益を計算（売却価格 - ローン残高）
        $netProceeds = $salePrice - $loanBalance;

        // コイン変動（純利益分を受け取る）
        $newCoinBalance = $user->current_coins + $netProceeds;
        $user->update(['current_coins' => $newCoinBalance]);

        // ユーザー保有不動産を売却済みに更新
        $userRealEstate->update([
            'is_sold' => true,
            'sale_date' => now()->toDateString(),
            'sale_price' => $salePrice,
            'loan_balance' => 0 // ローン完済
        ]);

        // 取引履歴を記録
        RealEstateTradeHistory::create([
            'user_id' => $user->id,
            'user_real_estate_id' => $userRealEstate->id,
            'real_estate_id' => $userRealEstate->real_estate_id,
            'trade_type' => 'sell',
            'property_name' => $userRealEstate->property_name,
            'price' => $salePrice,
            'loan_amount' => null,
            'coin_payment' => 0,
            'coin_change' => $netProceeds,
            'coin_balance_after' => $newCoinBalance
        ]);

        // コイン履歴記録
        UserCoinHistory::create([
            'user_id' => $user->id,
            'amount' => $netProceeds,
            'transaction_type' => 'sell_real_estate',
            'description' => $userRealEstate->property_name . ' を売却'
        ]);

        // 元の物件を再度販売可能に（オプション）
        if ($userRealEstate->realEstate) {
            $userRealEstate->realEstate->update(['status' => 'available']);
        }

        return [
            'success' => true,
            'message' => $userRealEstate->property_name . ' を売却しました',
            'data' => [
                'property_name' => $userRealEstate->property_name,
                'purchase_price' => $userRealEstate->purchase_price,
                'sale_price' => $salePrice,
                'loan_balance' => $loanBalance,
                'net_proceeds' => round($netProceeds, 2),
                'profit_loss' => round($netProceeds - ($userRealEstate->purchase_price - $userRealEstate->total_loan_amount), 2),
                'remaining_coins' => $newCoinBalance
            ]
        ];
    }

    /**
     * ポートフォリオ取得
     * GET /api/real-estate-trading/portfolio
     */
    public function portfolio(Request $request)
    {
        // リクエストからuser_idを取得
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

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // 保有中の不動産のみ取得
        $userRealEstates = UserRealEstate::where('user_id', $userId)
            ->where('is_sold', false)
            ->with('realEstate')
            ->get();

        $portfolio = $userRealEstates->map(function ($userRealEstate) {
            $currentValue = $userRealEstate->calculateSalePrice();
            $equity = $currentValue - $userRealEstate->loan_balance;
            $totalInvested = $userRealEstate->purchase_price - $userRealEstate->total_loan_amount;
            $profitLoss = $equity - $totalInvested;

            // 週次の純家賃収入
            $weeklyNetRent = $userRealEstate->calculateWeeklyNetRent();

            // 現在の金利を取得
            $currentInterestRate = InterestRateHistory::getCurrentRate();
            $weeklyInterest = ($userRealEstate->loan_balance * $currentInterestRate) / (100 * 52);

            // 週次純利益（家賃収入 - ローン支払い - 管理費）
            $weeklyProfit = $weeklyNetRent - $userRealEstate->weekly_principal - $weeklyInterest - ($userRealEstate->management_cost / 4);

            return [
                'id' => $userRealEstate->id,
                'property_name' => $userRealEstate->property_name,
                'property_type' => $userRealEstate->property_type,
                'purchase_price' => $userRealEstate->purchase_price,
                'purchase_date' => $userRealEstate->purchase_date->format('Y-m-d'),
                'current_value' => round($currentValue, 2),
                'loan_balance' => $userRealEstate->loan_balance,
                'equity' => round($equity, 2),
                'total_invested' => $totalInvested,
                'profit_loss' => round($profitLoss, 2),
                'current_rent' => $userRealEstate->current_rent,
                'vacancy_rate' => $userRealEstate->vacancy_rate,
                'weekly_net_rent' => round($weeklyNetRent, 2),
                'weekly_principal' => $userRealEstate->weekly_principal,
                'weekly_interest' => round($weeklyInterest, 2),
                'management_cost' => $userRealEstate->management_cost,
                'weekly_profit' => round($weeklyProfit, 2),
                'weeks_owned' => $userRealEstate->weeks_owned,
                'land_demand' => $userRealEstate->land_demand,
                'building_age' => $userRealEstate->building_age
            ];
        });

        $totalInvested = $portfolio->sum('total_invested');
        $totalCurrentValue = $portfolio->sum('current_value');
        $totalLoanBalance = $portfolio->sum('loan_balance');
        $totalEquity = $portfolio->sum('equity');
        $totalProfitLoss = $portfolio->sum('profit_loss');
        $totalWeeklyProfit = $portfolio->sum('weekly_profit');

        return response()->json([
            'success' => true,
            'data' => [
                'current_coins' => $user->current_coins,
                'total_invested' => round($totalInvested, 2),
                'total_current_value' => round($totalCurrentValue, 2),
                'total_loan_balance' => round($totalLoanBalance, 2),
                'total_equity' => round($totalEquity, 2),
                'total_profit_loss' => round($totalProfitLoss, 2),
                'total_weekly_profit' => round($totalWeeklyProfit, 2),
                'property_count' => $portfolio->count(),
                'holdings' => $portfolio
            ]
        ]);
    }

    /**
     * 取引履歴取得
     * GET /api/real-estate-trading/history
     */
    public function history(Request $request)
    {
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

        $limit = $request->get('limit', 50);

        $history = RealEstateTradeHistory::where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($trade) {
                return [
                    'id' => $trade->id,
                    'property_name' => $trade->property_name,
                    'trade_type' => $trade->trade_type,
                    'price' => $trade->price,
                    'loan_amount' => $trade->loan_amount,
                    'coin_payment' => $trade->coin_payment,
                    'coin_change' => $trade->coin_change,
                    'coin_balance_after' => $trade->coin_balance_after,
                    'trade_date' => $trade->created_at->format('Y-m-d H:i:s')
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
}
