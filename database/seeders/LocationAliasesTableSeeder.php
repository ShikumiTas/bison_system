<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\City; // Cityモデルがある場合
use Carbon\Carbon;

class LocationAliasesTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 施設名と対応するJISコードの定義
        // 茨城県の件など、間違えやすいものを優先的に定義します
        $aliases = [
            // 茨城県
            ['alias_name' => '動物医薬品検査所', 'city_cd' => '08203'], // つくば市
            ['alias_name' => '高エネルギー加速器研究機構', 'city_cd' => '08203'],

            // 東京都
            ['alias_name' => '艦艇装備研究所', 'city_cd' => '13110'], // 目黒区
            ['alias_name' => '目黒基地', 'city_cd' => '13110'],
            ['alias_name' => '大手町合同庁舎', 'city_cd' => '13101'], // 千代田区

            // 栃木県
            ['alias_name' => '那須庁舎', 'city_cd' => '09210'], // 大田原市
            ['alias_name' => '那須合同庁舎', 'city_cd' => '09213'], // 那須塩原市
            ['alias_name' => '栃木県庁', 'city_cd' => '09201'], // 宇都宮市

            // 愛知県
            ['alias_name' => '渥美郵便局', 'city_cd' => '23231'], // 田原市
            ['alias_name' => '名古屋港管理組合', 'city_cd' => '23100'], // 名古屋市（便宜上）
        ];

        foreach ($aliases as $data) {
            // 1. 重複チェック（同じ施設名が登録済みならスキップ）
            $exists = DB::table('location_aliases')
                ->where('alias_name', $data['alias_name'])
                ->exists();

            if ($exists) {
                continue;
            }

            // 2. 整合性チェック（紐付ける city_cd が cities テーブルにあるか）
            $cityExists = DB::table('cities')
                ->where('city_cd', $data['city_cd'])
                ->exists();

            if (!$cityExists) {
                // city_cd が見つからない場合は警告を出してスキップ
                $this->command->warn("Skip: City code {$data['city_cd']} not found in cities table for {$data['alias_name']}");
                continue;
            }

            // 3. 挿入
            DB::table('location_aliases')->insert([
                'city_cd'  => $data['city_cd'],
                'alias_name' => $data['alias_name'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        
        $this->command->info('Location aliases seeded successfully.');
    }
}