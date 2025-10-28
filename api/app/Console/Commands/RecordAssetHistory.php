<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserStock;
use Illuminate\Support\Facades\DB;

class RecordAssetHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:record-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '全ユーザーの資産履歴を記録する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('資産履歴の記録を開始します...');

        $users = User::all();
        $recordedCount = 0;

        foreach ($users as $user) {
            // 保有株式の合計価値を計算
            $userStocks = UserStock::where('user_id', $user->id)
                ->where('quantity', '>', 0)
                ->with('stock')
                ->get();

            $totalStockValue = $userStocks->sum(function($userStock) {
                return $userStock->stock->current_price * $userStock->quantity;
            });

            $totalAssets = $user->current_coins + $totalStockValue;

            // 資産履歴を記録
            DB::table('asset_histories')->insert([
                'user_id' => $user->id,
                'total_assets' => $totalAssets,
                'stock_value' => $totalStockValue,
                'coin_balance' => $user->current_coins,
                'recorded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $recordedCount++;
        }

        $this->info("資産履歴の記録が完了しました。（{$recordedCount}ユーザー）");

        return 0;
    }
}
