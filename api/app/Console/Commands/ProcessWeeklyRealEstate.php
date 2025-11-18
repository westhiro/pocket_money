<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserRealEstate;
use App\Models\WeeklyRentIncome;
use App\Models\WeeklyLoanPayment;
use App\Models\InterestRateHistory;
use App\Models\User;
use App\Models\UserCoinHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessWeeklyRealEstate extends Command
{
    protected $signature = 'real-estate:process-weekly {--force : 強制的に処理する}';
    protected $description = '週次不動産処理: 家賃収入、ローン支払い、金利変動';

    public function handle()
    {
        $this->info('週次不動産処理を開始します...');

        $force = $this->option('force');
        $now = Carbon::now();
        $weekStartDate = $now->startOfWeek()->toDateString();

        // 今週既に処理されているかチェック
        if (!$force) {
            $alreadyProcessed = WeeklyRentIncome::where('week_start_date', $weekStartDate)->exists();
            if ($alreadyProcessed) {
                $this->warn('今週は既に処理されています。強制実行するには --force オプションを使用してください。');
                return 0;
            }
        }

        DB::beginTransaction();
        try {
            // 1. 金利変動処理
            $this->updateInterestRate($now);

            // 2. 保有中の全不動産を処理
            $userRealEstates = UserRealEstate::where('is_sold', false)
                ->with('user')
                ->get();

            $processedCount = 0;

            foreach ($userRealEstates as $userRealEstate) {
                $this->processProperty($userRealEstate, $weekStartDate, $now);
                $processedCount++;

                $this->info("[{$processedCount}/{$userRealEstates->count()}] {$userRealEstate->property_name} を処理完了");
            }

            DB::commit();
            $this->info("\n週次不動産処理完了！ {$processedCount}件の物件を処理しました。");
            return 0;

        } catch (\Exception $e) {
            DB::rollback();
            $this->error('エラーが発生しました: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * 金利変動処理
     */
    private function updateInterestRate($date)
    {
        $this->info("\n金利変動処理中...");

        // 0.5% ~ 3.0%の範囲でランダムな金利を生成
        $newRate = round(rand(50, 300) / 100, 2);

        // 既存の金利データがあるか確認
        $existingRate = InterestRateHistory::where('effective_date', $date->toDateString())->first();

        if ($existingRate) {
            // 既存の金利を更新
            $existingRate->update(['interest_rate' => $newRate]);
            $this->info("金利を更新: {$newRate}%");
        } else {
            // 新規金利データを作成
            InterestRateHistory::create([
                'interest_rate' => $newRate,
                'effective_date' => $date->toDateString()
            ]);
            $this->info("新規金利を設定: {$newRate}%");
        }
    }

    /**
     * 個別物件の週次処理
     */
    private function processProperty($userRealEstate, $weekStartDate, $now)
    {
        $user = $userRealEstate->user;

        // 1. 家賃収入処理
        $this->processRentIncome($userRealEstate, $user, $weekStartDate);

        // 2. ローン支払い処理
        $this->processLoanPayment($userRealEstate, $user, $now);

        // 3. 保有週数を更新
        $userRealEstate->increment('weeks_owned');
    }

    /**
     * 家賃収入処理
     */
    private function processRentIncome($userRealEstate, $user, $weekStartDate)
    {
        // 月次家賃を週次に換算（月を4週で割る）
        $weeklyBaseRent = $userRealEstate->current_rent / 4;

        // 空室率を適用
        $vacancyDeduction = $weeklyBaseRent * ($userRealEstate->vacancy_rate / 100);
        $netIncome = $weeklyBaseRent - $vacancyDeduction;

        // ユーザーのコインに家賃収入を加算
        $newCoinBalance = $user->current_coins + $netIncome;
        $user->update(['current_coins' => $newCoinBalance]);

        // 家賃収入履歴を記録
        WeeklyRentIncome::create([
            'user_id' => $user->id,
            'user_real_estate_id' => $userRealEstate->id,
            'base_rent' => $weeklyBaseRent,
            'vacancy_deduction' => $vacancyDeduction,
            'net_income' => $netIncome,
            'vacancy_rate' => $userRealEstate->vacancy_rate,
            'week_start_date' => $weekStartDate
        ]);

        // コイン履歴記録
        UserCoinHistory::create([
            'user_id' => $user->id,
            'amount' => $netIncome,
            'transaction_type' => 'rent_income',
            'description' => $userRealEstate->property_name . ' の家賃収入'
        ]);
    }

    /**
     * ローン支払い処理
     */
    private function processLoanPayment($userRealEstate, $user, $date)
    {
        // ローン残高がない場合はスキップ
        if ($userRealEstate->loan_balance <= 0) {
            return;
        }

        // 元本支払額
        $principalPayment = $userRealEstate->weekly_principal;

        // 現在の金利を取得
        $currentRate = InterestRateHistory::getCurrentRate();

        // 金利支払額を計算（年利を週利に換算）
        $interestPayment = ($userRealEstate->loan_balance * $currentRate) / (100 * 52);

        // 総支払額
        $totalPayment = $principalPayment + $interestPayment;

        // コイン残高チェック
        if ($user->current_coins < $totalPayment) {
            $this->warn("警告: {$user->name} のコインが不足しています（物件: {$userRealEstate->property_name}）");
            // 残高不足の場合でも処理を続行（負債として記録）
        }

        // ユーザーのコインからローン支払いを引き落とし
        $newCoinBalance = $user->current_coins - $totalPayment;
        $user->update(['current_coins' => $newCoinBalance]);

        // ローン残高を更新（元本のみ減少）
        $newLoanBalance = max(0, $userRealEstate->loan_balance - $principalPayment);
        $userRealEstate->update(['loan_balance' => $newLoanBalance]);

        // ローン支払い履歴を記録
        WeeklyLoanPayment::create([
            'user_id' => $user->id,
            'user_real_estate_id' => $userRealEstate->id,
            'principal_payment' => $principalPayment,
            'interest_payment' => $interestPayment,
            'interest_rate' => $currentRate,
            'remaining_balance' => $newLoanBalance,
            'payment_date' => $date->toDateString()
        ]);

        // コイン履歴記録
        UserCoinHistory::create([
            'user_id' => $user->id,
            'amount' => -$totalPayment,
            'transaction_type' => 'loan_payment',
            'description' => $userRealEstate->property_name . ' のローン支払い（元本: ' . round($principalPayment, 2) . '万円 + 金利: ' . round($interestPayment, 2) . '万円）'
        ]);
    }
}
