<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RealEstate;

class RealEstateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $properties = [
            // 高級マンション
            [
                'property_name' => 'プレミアムタワー港区',
                'property_type' => 'luxury',
                'base_price' => 8000,
                'land_demand' => 'rising',
                'building_age' => 'new',
                'square_meters' => 65,
                'management_fee_per_sqm' => 220,
                'repair_reserve_per_sqm' => 230,
                'location_x' => 100,
                'location_y' => 150
            ],
            [
                'property_name' => 'グランドヒルズ渋谷',
                'property_type' => 'luxury',
                'base_price' => 8000,
                'land_demand' => 'normal',
                'building_age' => 'semi_new',
                'square_meters' => 60,
                'management_fee_per_sqm' => 200,
                'repair_reserve_per_sqm' => 220,
                'location_x' => 120,
                'location_y' => 140
            ],
            [
                'property_name' => 'エクセレント目黒',
                'property_type' => 'luxury',
                'base_price' => 8000,
                'land_demand' => 'falling',
                'building_age' => 'old',
                'square_meters' => 70,
                'management_fee_per_sqm' => 190,
                'repair_reserve_per_sqm' => 250,
                'location_x' => 110,
                'location_y' => 130
            ],

            // 通常マンション
            [
                'property_name' => 'コンフォート新宿',
                'property_type' => 'standard',
                'base_price' => 5000,
                'land_demand' => 'rising',
                'building_age' => 'new',
                'square_meters' => 50,
                'management_fee_per_sqm' => 160,
                'repair_reserve_per_sqm' => 200,
                'location_x' => 80,
                'location_y' => 120
            ],
            [
                'property_name' => 'サニーコート中野',
                'property_type' => 'standard',
                'base_price' => 5000,
                'land_demand' => 'normal',
                'building_age' => 'semi_new',
                'square_meters' => 45,
                'management_fee_per_sqm' => 150,
                'repair_reserve_per_sqm' => 190,
                'location_x' => 90,
                'location_y' => 110
            ],
            [
                'property_name' => 'メゾン杉並',
                'property_type' => 'standard',
                'base_price' => 5000,
                'land_demand' => 'falling',
                'building_age' => 'old',
                'square_meters' => 55,
                'management_fee_per_sqm' => 140,
                'repair_reserve_per_sqm' => 210,
                'location_x' => 70,
                'location_y' => 100
            ],
            [
                'property_name' => 'パークサイド世田谷',
                'property_type' => 'standard',
                'base_price' => 5000,
                'land_demand' => 'normal',
                'building_age' => 'new',
                'square_meters' => 48,
                'management_fee_per_sqm' => 155,
                'repair_reserve_per_sqm' => 195,
                'location_x' => 85,
                'location_y' => 105
            ],

            // 低価格マンション
            [
                'property_name' => 'フレンドリー練馬',
                'property_type' => 'budget',
                'base_price' => 2000,
                'land_demand' => 'rising',
                'building_age' => 'new',
                'square_meters' => 35,
                'management_fee_per_sqm' => 145,
                'repair_reserve_per_sqm' => 200,
                'location_x' => 60,
                'location_y' => 90
            ],
            [
                'property_name' => 'エコノミー板橋',
                'property_type' => 'budget',
                'base_price' => 2000,
                'land_demand' => 'normal',
                'building_age' => 'semi_new',
                'square_meters' => 32,
                'management_fee_per_sqm' => 135,
                'repair_reserve_per_sqm' => 190,
                'location_x' => 50,
                'location_y' => 80
            ],
            [
                'property_name' => 'バリュー足立',
                'property_type' => 'budget',
                'base_price' => 2000,
                'land_demand' => 'falling',
                'building_age' => 'old',
                'square_meters' => 38,
                'management_fee_per_sqm' => 125,
                'repair_reserve_per_sqm' => 220,
                'location_x' => 40,
                'location_y' => 70
            ],
            [
                'property_name' => 'コンパクト江戸川',
                'property_type' => 'budget',
                'base_price' => 2000,
                'land_demand' => 'normal',
                'building_age' => 'semi_new',
                'square_meters' => 30,
                'management_fee_per_sqm' => 130,
                'repair_reserve_per_sqm' => 185,
                'location_x' => 55,
                'location_y' => 75
            ],
            [
                'property_name' => 'ライトコート葛飾',
                'property_type' => 'budget',
                'base_price' => 2000,
                'land_demand' => 'rising',
                'building_age' => 'new',
                'square_meters' => 33,
                'management_fee_per_sqm' => 140,
                'repair_reserve_per_sqm' => 195,
                'location_x' => 45,
                'location_y' => 85
            ],
        ];

        foreach ($properties as $property) {
            // 既存のデータがあればスキップ
            $existing = RealEstate::where('property_name', $property['property_name'])->first();

            if ($existing) {
                echo "Property {$property['property_name']} already exists, skipping...\n";
                continue;
            }

            RealEstate::create([
                'property_name' => $property['property_name'],
                'property_type' => $property['property_type'],
                'base_price' => $property['base_price'],
                'land_demand' => $property['land_demand'],
                'building_age' => $property['building_age'],
                'square_meters' => $property['square_meters'],
                'management_fee_per_sqm' => $property['management_fee_per_sqm'],
                'repair_reserve_per_sqm' => $property['repair_reserve_per_sqm'],
                'location_x' => $property['location_x'],
                'location_y' => $property['location_y'],
                'status' => 'available'
            ]);

            echo "Created property: {$property['property_name']}\n";
        }
    }
}
