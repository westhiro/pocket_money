<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserRealEstate;
use App\Models\MonthlyCost;
use App\Models\User;
use App\Models\UserCoinHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessMonthlyRealEstate extends Command
{
    protected $signature = 'real-estate:process-monthly {--force : 強制的に処理する}';
    protected $description = '月次不動産処理: 管理費・修繕積立金、家賃相場変動、空室率再計算';

    public function handle()
    {
        $this->info('月次不動産処理を開始します...');

        $force = $this->option('force');
        $now = Carbon::now();
        $paymentDate = $now->startOfMonth()->toDateString();

        // 今月既に処理されているかチェック
        if (!$force) {
            $alreadyProcessed = MonthlyCost::where('payment_date', $paymentDate)->exists();
            if ($alreadyProcessed) {
                $this->warn('今月は既に処理されています。強制実行するには --force オプションを使用してください。');
                return 0;
            }
        }

        DB::beginTransaction();
        try {
            // 保有中の全不動産を処理
            $userRealEstates = UserRealEstate::where('is_sold', false)
                ->with('user', 'realEstate')
                ->get();

            $processedCount = 0;

            foreach ($userRealEstates as $userRealEstate) {
                $this->processProperty($userRealEstate, $paymentDate);
                $processedCount++;

                $this->info("[{$processedCount}/{$userRealEstates->count()}] {$userRealEstate->property_name} を処理完了");
            }

            DB::commit();
            $this->info("\n月次不動産処理完了！ {$processedCount}件の物件を処理しました。");
            return 0;

        } catch (\Exception $e) {
            DB::rollback();
            $this->error('エラーが発生しました: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * 個別物件の月次処理
     */
    private function processProperty($userRealEstate, $paymentDate)
    {
        $user = $userRealEstate->user;

        // 1. 管理費・修繕積立金支払い処理
        $this->processMonthlyCost($userRealEstate, $user, $paymentDate);

        // 2. 家賃相場変動処理
        $this->updateRentMarket($userRealEstate);

        // 3. 空室率再計算処理
        $this->recalculateVacancyRate($userRealEstate);
    }

    /**
     * 管理費・修繕積立金支払い処理
     */
    private function processMonthlyCost($userRealEstate, $user, $paymentDate)
    {
        // 元の不動産物件情報から平米数と単価を取得
        $squareMeters = $userRealEstate->square_meters;
        $realEstate = $userRealEstate->realEstate;

        if (!$realEstate) {
            // 物件情報がない場合は保存済みの管理費を使用
            $totalCost = $userRealEstate->management_cost;
            $managementFee = round($totalCost * 0.5); // 概算で半分ずつ
            $repairReserve = $totalCost - $managementFee;
        } else {
            // 管理費と修繕積立金を計算
            $managementFee = $realEstate->management_fee_per_sqm * $squareMeters;
            $repairReserve = $realEstate->repair_reserve_per_sqm * $squareMeters;
            $totalCost = $managementFee + $repairReserve;
        }

        // コイン残高チェック
        if ($user->current_coins < $totalCost) {
            $this->warn("警告: {$user->name} のコインが不足しています（物件: {$userRealEstate->property_name}）");
            // 残高不足の場合でも処理を続行（負債として記録）
        }

        // ユーザーのコインから引き落とし（万円単位に変換）
        $costInManYen = $totalCost / 10000;
        $newCoinBalance = $user->current_coins - $costInManYen;
        $user->update(['current_coins' => $newCoinBalance]);

        // 月次コスト履歴を記録
        MonthlyCost::create([
            'user_id' => $user->id,
            'user_real_estate_id' => $userRealEstate->id,
            'management_fee' => $managementFee,
            'repair_reserve' => $repairReserve,
            'total_cost' => $totalCost,
            'payment_date' => $paymentDate
        ]);

        // コイン履歴記録
        UserCoinHistory::create([
            'user_id' => $user->id,
            'amount' => -$costInManYen,
            'transaction_type' => 'property_cost',
            'description' => $userRealEstate->property_name . ' の管理費・修繕積立金（' . number_format($totalCost) . '円）'
        ]);
    }

    /**
     * 家賃相場変動処理
     */
    private function updateRentMarket($userRealEstate)
    {
        // ランダムに評価を決定
        $evaluations = ['good', 'normal', 'bad'];
        $randomEvaluation = $evaluations[array_rand($evaluations)];

        // 新しい利回りを計算
        $yieldRanges = [
            'rising' => ['good' => [4.0, 5.0], 'normal' => [3.0, 3.9], 'bad' => [2.0, 2.9]],
            'normal' => ['good' => [5.0, 6.5], 'normal' => [4.0, 4.9], 'bad' => [3.0, 3.9]],
            'falling' => ['good' => [8.0, 10.0], 'normal' => [6.0, 7.9], 'bad' => [5.0, 5.9]]
        ];

        $range = $yieldRanges[$userRealEstate->land_demand][$randomEvaluation] ?? [4.0, 5.0];
        $newYieldRate = round(rand($range[0] * 100, $range[1] * 100) / 100, 2);

        // 新しい家賃相場を計算
        $newMonthlyRent = ($newYieldRate * $userRealEstate->purchase_price) / (100 * 12);

        // ユーザーが設定した家賃変更率を維持しつつ、相場に合わせる
        // （ユーザーが相場より高く/低く設定している場合、その差を維持）
        $currentRentChangeRate = $userRealEstate->rent_change_rate;

        // 家賃相場を更新（変更率は維持）
        $userRealEstate->update([
            'yield_rate' => $newYieldRate,
            'current_rent' => round($newMonthlyRent * (1 + $currentRentChangeRate / 100), 2)
        ]);

        $this->info("  家賃相場更新: {$userRealEstate->property_name} - 利回り {$newYieldRate}% - 家賃 " . round($newMonthlyRent, 2) . "万円/月");
    }

    /**
     * 空室率再計算処理
     */
    private function recalculateVacancyRate($userRealEstate)
    {
        // 土地需要による空室率
        $landDemandRate = match($userRealEstate->land_demand) {
            'rising' => 0,
            'normal' => 5,
            'falling' => 10,
            default => 5
        };

        // 築年数による空室率
        $buildingAgeRate = match($userRealEstate->building_age) {
            'new' => 0,
            'semi_new' => 5,
            'old' => 10,
            default => 5
        };

        // 家賃変更率（相場からの乖離による空室率への影響）
        $rentChangeRate = $userRealEstate->rent_change_rate;

        // 新しい空室率を計算
        $newVacancyRate = $landDemandRate + $buildingAgeRate + $rentChangeRate;

        // 空室率は0%～100%の範囲に制限
        $newVacancyRate = max(0, min(100, $newVacancyRate));

        // 空室率を更新
        $userRealEstate->update(['vacancy_rate' => $newVacancyRate]);

        $this->info("  空室率再計算: {$userRealEstate->property_name} - {$newVacancyRate}%");
    }
}
