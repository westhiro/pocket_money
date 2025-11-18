<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InterestRateHistory;
use Carbon\Carbon;

class InterestRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 過去52週間（1年分）の金利履歴を生成
        $startDate = Carbon::now()->subWeeks(52);

        for ($i = 0; $i <= 52; $i++) {
            $effectiveDate = $startDate->copy()->addWeeks($i);

            // 既存のデータがあればスキップ
            $existing = InterestRateHistory::where('effective_date', $effectiveDate->format('Y-m-d'))->first();

            if ($existing) {
                continue;
            }

            // 0.5% ~ 3.0%の範囲でランダムな金利を生成
            $interestRate = round(rand(50, 300) / 100, 2);

            InterestRateHistory::create([
                'interest_rate' => $interestRate,
                'effective_date' => $effectiveDate->format('Y-m-d')
            ]);
        }

        // 来週以降の金利も少し先まで生成（次の4週間分）
        for ($i = 1; $i <= 4; $i++) {
            $effectiveDate = Carbon::now()->addWeeks($i);

            $existing = InterestRateHistory::where('effective_date', $effectiveDate->format('Y-m-d'))->first();

            if ($existing) {
                continue;
            }

            $interestRate = round(rand(50, 300) / 100, 2);

            InterestRateHistory::create([
                'interest_rate' => $interestRate,
                'effective_date' => $effectiveDate->format('Y-m-d')
            ]);
        }

        echo "Created interest rate history data\n";
    }
}
