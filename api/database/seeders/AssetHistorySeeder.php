<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserStock;
use Illuminate\Support\Facades\DB;

class AssetHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('過去7日分の資産履歴を生成します（各日の終値のみ）...');

        // 既存の資産履歴を削除
        DB::table('asset_histories')->delete();

        $users = User::all();

        foreach ($users as $user) {
            // 現在の資産を計算
            $userStocks = UserStock::where('user_id', $user->id)
                ->where('quantity', '>', 0)
                ->with('stock')
                ->get();

            $currentStockValue = $userStocks->sum(function($userStock) {
                return $userStock->stock->current_price * $userStock->quantity;
            });

            $currentTotalAssets = $user->current_coins + $currentStockValue;

            // 7日前の資産は現在の85%とする
            $startAssets = $currentTotalAssets * 0.85;

            // 過去7日分のデータを生成（各日の終値 = 23:00のデータ）
            for ($i = 7; $i >= 1; $i--) {
                // 各日の23:00（終値）に設定
                $date = now()->subDays($i)->setHour(23)->setMinute(0)->setSecond(0);

                // 進捗率（0から1）
                $progress = (7 - $i) / 7;

                // 基本的な増加傾向
                $baseAssets = $startAssets + ($currentTotalAssets - $startAssets) * $progress;

                // ランダムな変動を加える（±5%）
                $variation = $baseAssets * (mt_rand(-500, 500) / 10000);
                $totalAssets = max(0, $baseAssets + $variation);

                // 株式とコインの比率を維持
                $stockRatio = $currentStockValue > 0 ? $currentStockValue / $currentTotalAssets : 0;
                $stockValue = $totalAssets * $stockRatio;
                $coinBalance = $totalAssets * (1 - $stockRatio);

                DB::table('asset_histories')->insert([
                    'user_id' => $user->id,
                    'total_assets' => $totalAssets,
                    'stock_value' => $stockValue,
                    'coin_balance' => $coinBalance,
                    'recorded_at' => $date,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->command->info("ユーザーID {$user->id} の資産履歴を生成しました（7日分）");
        }

        $this->command->info('資産履歴の生成が完了しました（各日の終値データ）');
    }
}
