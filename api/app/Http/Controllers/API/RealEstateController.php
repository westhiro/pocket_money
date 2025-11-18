<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RealEstate;
use App\Models\InterestRateHistory;
use Illuminate\Http\Request;

class RealEstateController extends Controller
{
    /**
     * 物件一覧取得
     * GET /api/real-estates
     */
    public function index(Request $request)
    {
        $query = RealEstate::query();

        // ステータスでフィルタリング（デフォルト: available）
        $status = $request->get('status', 'available');
        if ($status) {
            $query->where('status', $status);
        }

        // 物件タイプでフィルタリング
        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        // 土地需要でフィルタリング
        if ($request->has('land_demand')) {
            $query->where('land_demand', $request->land_demand);
        }

        // 築年数でフィルタリング
        if ($request->has('building_age')) {
            $query->where('building_age', $request->building_age);
        }

        // 価格範囲でフィルタリング
        if ($request->has('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }

        $realEstates = $query->get();

        $formattedData = $realEstates->map(function ($property) {
            $purchasePrice = $property->calculatePurchasePrice();
            $monthlyCost = $property->calculateMonthlyCost();
            $yieldRate = $property->calculateYieldRate('normal');
            $monthlyRent = ($yieldRate * $purchasePrice) / (100 * 12);

            return [
                'id' => $property->id,
                'property_name' => $property->property_name,
                'property_type' => $property->property_type,
                'base_price' => $property->base_price,
                'purchase_price' => round($purchasePrice, 2),
                'land_demand' => $property->land_demand,
                'building_age' => $property->building_age,
                'square_meters' => $property->square_meters,
                'management_fee_per_sqm' => $property->management_fee_per_sqm,
                'repair_reserve_per_sqm' => $property->repair_reserve_per_sqm,
                'monthly_cost' => $monthlyCost,
                'estimated_yield_rate' => $yieldRate,
                'estimated_monthly_rent' => round($monthlyRent, 2),
                'location' => [
                    'x' => $property->location_x,
                    'y' => $property->location_y
                ],
                'status' => $property->status
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData
        ]);
    }

    /**
     * 物件詳細取得
     * GET /api/real-estates/{id}
     */
    public function show($id)
    {
        $property = RealEstate::find($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => '物件が見つかりません'
            ], 404);
        }

        $purchasePrice = $property->calculatePurchasePrice();
        $monthlyCost = $property->calculateMonthlyCost();

        // 異なる評価での利回りを計算
        $yieldRates = [
            'good' => $property->calculateYieldRate('good'),
            'normal' => $property->calculateYieldRate('normal'),
            'bad' => $property->calculateYieldRate('bad')
        ];

        // ローン計算
        $loanPeriodWeeks = 480; // 40年 = 480週
        $weeklyPrincipal = $purchasePrice / $loanPeriodWeeks;

        // 各評価での月次家賃収入を計算
        $monthlyRents = [
            'good' => round(($yieldRates['good'] * $purchasePrice) / (100 * 12), 2),
            'normal' => round(($yieldRates['normal'] * $purchasePrice) / (100 * 12), 2),
            'bad' => round(($yieldRates['bad'] * $purchasePrice) / (100 * 12), 2)
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $property->id,
                'property_name' => $property->property_name,
                'property_type' => $property->property_type,
                'base_price' => $property->base_price,
                'purchase_price' => round($purchasePrice, 2),
                'land_demand' => $property->land_demand,
                'building_age' => $property->building_age,
                'square_meters' => $property->square_meters,
                'management_fee_per_sqm' => $property->management_fee_per_sqm,
                'repair_reserve_per_sqm' => $property->repair_reserve_per_sqm,
                'monthly_cost' => $monthlyCost,
                'location' => [
                    'x' => $property->location_x,
                    'y' => $property->location_y
                ],
                'status' => $property->status,
                'yield_rates' => $yieldRates,
                'monthly_rents' => $monthlyRents,
                'loan_info' => [
                    'total_amount' => round($purchasePrice, 2),
                    'period_weeks' => $loanPeriodWeeks,
                    'weekly_principal' => round($weeklyPrincipal, 2)
                ],
                'property_type_info' => $this->getPropertyTypeInfo($property->property_type)
            ]
        ]);
    }

    /**
     * 物件タイプ情報取得
     */
    private function getPropertyTypeInfo($type)
    {
        $typeInfo = [
            'luxury' => [
                'name' => '高級マンション',
                'base_price' => 8000,
                'description' => '都心の一等地に位置する高級マンション'
            ],
            'standard' => [
                'name' => '通常マンション',
                'base_price' => 5000,
                'description' => 'バランスの取れた標準的なマンション'
            ],
            'budget' => [
                'name' => '低価格マンション',
                'base_price' => 2000,
                'description' => '手頃な価格で購入できるマンション'
            ]
        ];

        return $typeInfo[$type] ?? null;
    }

    /**
     * 現在の金利取得
     * GET /api/real-estates/current-interest-rate
     */
    public function getCurrentInterestRate()
    {
        $currentRate = InterestRateHistory::getCurrentRate();

        return response()->json([
            'success' => true,
            'data' => [
                'interest_rate' => $currentRate,
                'effective_date' => now()->toDateString()
            ]
        ]);
    }
}
