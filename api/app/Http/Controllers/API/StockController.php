<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Industry;
use Illuminate\Http\Request;

class StockController extends Controller
{
    // 全株式データ取得
    public function index()
    {
        $stocks = Stock::with(['industry', 'priceHistory' => function($query) {
            $query->latest('recorded_at')->take(7); // 直近1週間のデータ
        }])->get();

        return response()->json([
            'success' => true,
            'data' => $stocks->map(function ($stock) {
                // 前日比を計算
                $priceChange = 0;
                $priceChangePercent = 0;
                
                if ($stock->priceHistory->count() >= 2) {
                    $currentPrice = $stock->current_price;
                    $previousPrice = $stock->priceHistory[1]->price; // 2番目に新しいレコード
                    $priceChange = $currentPrice - $previousPrice;
                    $priceChangePercent = $previousPrice > 0 ? (($priceChange / $previousPrice) * 100) : 0;
                }
                
                return [
                    'id' => $stock->id,
                    'company_name' => $stock->company_name,
                    'stock_symbol' => $stock->stock_symbol,
                    'current_price' => $stock->current_price,
                    'description' => $stock->description,
                    'industry' => [
                        'id' => $stock->industry->id,
                        'name' => $stock->industry->name,
                        'icon' => $stock->industry->icon,
                    ],
                    'price_change' => round($priceChange),
                    'price_change_percent' => round($priceChangePercent),
                    'price_history' => $stock->priceHistory->map(function ($history) {
                        return [
                            'price' => $history->price,
                            'recorded_at' => $history->recorded_at->format('Y-m-d H:i:s')
                        ];
                    })
                ];
            })
        ]);
    }

    // 個別株式詳細取得
    public function show($id)
    {
        $stock = Stock::with(['industry', 'priceHistory' => function($query) {
            $query->latest('recorded_at')->take(30);
        }])->find($id);

        if (!$stock) {
            return response()->json([
                'success' => false,
                'message' => '株式が見つかりません'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $stock->id,
                'company_name' => $stock->company_name,
                'stock_symbol' => $stock->stock_symbol,
                'current_price' => $stock->current_price,
                'description' => $stock->description,
                'industry' => [
                    'id' => $stock->industry->id,
                    'name' => $stock->industry->name,
                    'icon' => $stock->industry->icon,
                ],
                'price_change' => $stock->getPriceChangePercentage(),
                'chart_data' => $stock->priceHistory->map(function ($history) {
                    return [
                        'date' => $history->recorded_at->format('Y-m-d H:i'),
                        'price' => $history->price,
                        'change' => $history->change_percentage
                    ];
                })
            ]
        ]);
    }

    // 業界別株式取得
    public function byIndustry($industryId)
    {
        $stocks = Stock::with('industry')
            ->where('industry_id', $industryId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stocks
        ]);
    }

    // チャートデータ取得（期間別）
    public function chart($id, $period)
    {
        $stock = Stock::find($id);

        if (!$stock) {
            return response()->json([
                'success' => false,
                'message' => '株式が見つかりません'
            ], 404);
        }

        // 期間別の設定：表示するポイント数（時間数）
        $periodConfig = [
            '1w' => 7,       // 1週間：7時間分 = 7ポイント
            '2w' => 15,      // 半月：15時間分 = 15ポイント
            '1m' => 30       // 1ヶ月：30時間分 = 30ポイント
        ];

        $hoursNeeded = $periodConfig[$period] ?? $periodConfig['1m'];

        // 指定時間数分のデータを取得（1時間ごと）
        $allData = $stock->priceHistory()
            ->orderBy('recorded_at', 'desc')
            ->take($hoursNeeded)
            ->get()
            ->reverse()  // 古い順に並び替え
            ->values();  // インデックスをリセット

        // データをフォーマット（全ての時間データを使用）
        $formattedData = $allData->map(function ($history) use ($period) {
            // 日付と時刻を含めてフォーマット
            return [
                'date' => $history->recorded_at->format('Y-m-d H:i'),
                'price' => (float) $history->price,
                'change' => (float) ($history->change_percentage ?? 0)
            ];
        });

        // 現在価格を最新履歴と同期
        $latestHistory = $stock->priceHistory()->latest('recorded_at')->first();
        if ($latestHistory) {
            $stock->update(['current_price' => $latestHistory->price]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'stock_id' => $stock->id,
                'company_name' => $stock->company_name,
                'stock_symbol' => $stock->stock_symbol,
                'period' => $period,
                'chart_data' => $formattedData,
                'current_price' => $stock->current_price,
                'price_change' => $stock->getPriceChangePercentage()
            ]
        ]);
    }
}
