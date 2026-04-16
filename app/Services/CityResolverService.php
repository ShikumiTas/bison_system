<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CityResolverService
{
    private $cityCache = null;
    private $aliasCache = null;
    private $prefs = null;

    public function resolve(string $rawAddress): ?string
    {
        $address = $this->cleanAddress($rawAddress);
        if (empty($address)) return null;

        $this->loadMasterData();

        // 1. 都道府県プレフィックスの特定（鉄壁のガード）
        $targetPrefPrefix = null;
        foreach ($this->prefs as $prefCode => $prefName) {
            if (mb_strpos($address, $prefName) !== false) {
                $targetPrefPrefix = $prefCode;
                break;
            }
        }

        // 2. 市区町村マスタで判定（長い順に回すことで誤判定を抑制）
        foreach ($this->cityCache as $name => $code) {
            // 県名が判明しているなら、他県のコードはスルー
            if ($targetPrefPrefix && strpos($code, $targetPrefPrefix) !== 0) {
                continue;
            }

            // A. フルネームで一致（例：那須塩原市、横浜市神奈川区）
            if (mb_strpos($address, $name) !== false) {
                return $code;
            }

            // B. 市・郡・支庁を削った「短縮名」で判定
            // 「横浜市神奈川区」→「神奈川区」
            // 「那須郡那須町」→「那須町」
            $shortName = preg_replace('/^.*?[市郡支庁]/u', '', $name);
            
            // 1文字（北区の「北」など）で反応すると危険なので、2文字以上で判定
            if ($shortName !== $name && mb_strlen($shortName) > 1) {
                if (mb_strpos($address, $shortName) !== false) {
                    return $code;
                }
            }
        }

        // 3. エイリアスマスタで判定（マスタにない特殊な表記を救済）
        foreach ($this->aliasCache as $aliasName => $code) {
            if ($targetPrefPrefix && strpos($code, $targetPrefPrefix) !== 0) {
                continue;
            }
            if (mb_strpos($address, $aliasName) !== false) {
                return $code;
            }
        }

        return null; 
    }

    private function loadMasterData()
    {
        if ($this->cityCache !== null) return;

        $this->prefs = config('prefs') ?: [];

        $this->cityCache = DB::table('cities')
            ->orderByRaw('LENGTH(name) DESC')
            ->pluck('city_cd', 'name')
            ->toArray();

        $this->aliasCache = DB::table('location_aliases')
            ->orderByRaw('LENGTH(alias_name) DESC')
            ->pluck('city_cd', 'alias_name')
            ->toArray();
    }

    private function cleanAddress(string $text): string
    {
        $text = str_replace(["\r", "\n", " ", "　", "?"], '', $text);
        return trim(mb_convert_kana($text, 'as'));
    }
}