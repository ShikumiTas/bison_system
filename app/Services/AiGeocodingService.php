<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiGeocodingService
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key'); // configに設定
    }

public function guessLocation(string $title, string $org, string $loc): ?array
    {
        $prompt = <<<EOT
以下の入札案件情報から、履行場所の市区町村を特定し、JSON形式で回答してください。
タイトル: {$title}
発注機関: {$org}
履行場所: {$loc}

【回答ルール】
1. 施設名や地名から市区町村コード（5桁）が100%確実な場合のみ回答すること。
2. 返却形式は以下のJSONのみ。
{"alias_name": "特定した施設名や略称", "city_cd": "5桁の自治体コード"}
3. 特定できない場合は "null" とだけ回答してください。
EOT;

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$this->apiKey}", [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            // 1. レスポンスを受け取った直後のログ
            Log::info("AI Response Status: " . $response->status());

            if ($response->successful()) {
                // 2. 全体の中身を一度ログに出す（これで構造が見えます）
                $resultJson = $response->json();
                Log::info("AI Raw Result: " . json_encode($resultJson, JSON_UNESCAPED_UNICODE));

                $text = $resultJson['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if (!$text) {
                    Log::error("AI Response Text is empty");
                    return null;
                }

                // JSON部分だけを抽出
                if (preg_match('/\{.*\}/s', $text, $matches)) {
                    $decoded = json_decode($matches[0], true);
                    Log::info("AI Decoded Success: " . json_encode($decoded, JSON_UNESCAPED_UNICODE));
                    return $decoded;
                }

                Log::warning("AI returned text, but no JSON found: " . $text);
            } else {
                Log::error("AI API Call Failed: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("AI Geocoding Exception: " . $e->getMessage());
        }

        return null;
    }
}