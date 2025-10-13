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

        // 期間別の設定：[取得日数, 表示ポイント数, 間隔（日）]
        $periodConfig = [
            '1w' => ['days' => 7, 'points' => 7, 'interval' => 1],      // 1週間：7日分、7ポイント、1日間隔
            '1m' => ['days' => 30, 'points' => 30, 'interval' => 1],    // 1ヶ月：30日分、30ポイント、1日間隔
            '1y' => ['days' => 365, 'points' => 12, 'interval' => 30]   // 1年：365日分、12ポイント、30日間隔（月次）
        ];

        $config = $periodConfig[$period] ?? $periodConfig['1m'];
        $days = $config['days'];
        $targetPoints = $config['points'];
        $interval = $config['interval'];

        // 全データから指定日数分を取得
        $allData = $stock->priceHistory()
            ->orderBy('recorded_at', 'desc')
            ->take($days)
            ->get()
            ->reverse()  // 古い順に並び替え
            ->values();  // インデックスをリセット

        // 期間に応じてデータを間引く
        $chartData = collect();
        if ($period === '1y') {
            // 1年の場合：月次データを取得（約30日間隔）
            for ($i = 0; $i < $targetPoints && $i * $interval < $allData->count(); $i++) {
                $index = $i * $interval;
                if ($index < $allData->count()) {
                    $chartData->push($allData[$index]);
                }
            }
            
            // 最新のデータを必ず含める
            if ($allData->count() > 0) {
                $latestData = $allData->last();
                if (!$chartData->contains('recorded_at', $latestData->recorded_at)) {
                    $chartData->push($latestData);
                }
            }
        } else {
            // 1週間・1ヶ月の場合：全データをそのまま使用
            $chartData = $allData;
        }

        // データをフォーマット
        $formattedData = $chartData->map(function ($history) use ($period) {
            $dateFormat = $period === '1w' ? 'Y-m-d H:i' : 'Y-m-d';
            return [
                'date' => $history->recorded_at->format($dateFormat),
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
