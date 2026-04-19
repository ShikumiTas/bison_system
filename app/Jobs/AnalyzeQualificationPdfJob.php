<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class AnalyzeQualificationPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 最大試行回数
     */
    public $tries = 3;

    /**
     * リトライ待ち時間
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    protected string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("PDF解析Jobを開始しました: {$this->filePath}");

        if (!file_exists($this->filePath)) {
            Log::error("解析対象のPDFファイルが見つかりません。");
            return;
        }

        $pdfBase64 = base64_encode(file_get_contents($this->filePath));
        $apiKey = config('services.gemini.key');
        
        // Gemini API呼び出し
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            // 修正後：2.5-flash を指定
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'inlineData' => [
                                    'mimeType' => 'application/pdf',
                                    'data' => $pdfBase64
                                ]
                            ],
                            [
                                'text' => $this->getPrompt()
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json'
                ]
            ]);

        if ($response->failed()) {
            Log::error("Gemini APIのリクエストに失敗しました: " . $response->body());
            throw new Exception("Gemini API error.");
        }

        $resultJson = $response->json('candidates.0.content.parts.0.text');
        Log::debug("Geminiからの生レスポンス: " . $resultJson);

        $data = json_decode($resultJson, true);

        if (!$data) {
            Log::error("JSONデコード失敗");
            throw new Exception("JSON decode error.");
        }

        $fileName = 'qualifications/' . basename($this->filePath);
        Storage::disk('local_secure')->put($fileName, file_get_contents($this->filePath));
        
        // 複数エントリー対応
        if (isset($data[0]) && is_array($data[0])) {
            foreach ($data as $item) {
                $item['pdf_path'] = $fileName;
                $this->saveToDatabase($item);
            }
        } else {
            $data['pdf_path'] = $fileName;
            $this->saveToDatabase($data);
        }

        @unlink($this->filePath);
        Log::info("PDF解析Jobが正常に完了しました。");
    }

    private function saveToDatabase(array $data): void
    {
        \App\Models\Qualification::create([
            'authority_name' => $data['authority_name'] ?? '名称未設定',
            'license_name'   => $data['license_name'] ?? null,
            'trader_code'    => $data['trader_code'] ?? null,
            'valid_from'     => $data['valid_from'] ?? null,
            'valid_to'       => $data['valid_to'] ?? null,
            'business_items' => $data['business_items'] ?? [], 
            'pdf_path'       => $data['pdf_path'] ?? null,
        ]);
        
        $auth = $data['authority_name'] ?? 'Unknown';
        $code = $data['trader_code'] ?? 'NoCode';
        Log::info("DB保存完了: {$auth} (業者番号: {$code})");
    }

    /**
     * AIへのプロンプト（指示書）
     */
    private function getPrompt(): string
    {
        return <<<PROMPT
あなたは優秀なデータ構造化AIです。
アップロードされた資格情報のPDFから、以下のJSONフォーマットに厳密に従ってデータを抽出してください。
存在しない項目や判断できない項目は null にしてください。
日付は和暦（令和○年など）で書かれている場合は、必ず YYYY-MM-DD 形式の西暦に変換してください。

【重要：構造ルール】
1. 1つのPDF内に「建設工事」と「物品製造等・役務」など、複数の業者番号（登録内容）が並記されている場合は、それらを別々のオブジェクトとして抽出し、必ずそれらを一つの配列（ [{}, {}] ）にまとめて出力してください。
2. 業者番号（trader_code）が複数ある場合、それらは別々のエントリーとして扱う必要があります。

【建設工事の資格に関する超重要ルール】
・建設工事の資格の場合、土木や建築などの「工種（業種）」ごとに、必ず「総合点数（審査数値）」や「等級（ランク：A, B, C、またはア, イ, ウなど）」が存在します。
・申請していない工種や、資格のない工種（空白や斜線、ゼロになっているもの）は、絶対に `business_items` に含めないでください。

【ランク・等級の厳格ルール】
1. grade フィールドには「A」「B」「C」「D」といった「等級」のみを入れてください。
2. 「777位」のような「順位」は絶対に grade に入れないでください。不要です。
3. PDF内に「等級」や「ランク」が明記されていない場合は、点数から勝手にランクを推定せず、潔く null にしてください。
4. 推定が許されるのは、あなたがその機関の公式な格付基準を確実に把握しており、点数から100%確定できる場合のみです。その場合は `grade_note` に理由を書いてください。それ以外は null です。

【出力フォーマット】
{
  "authority_name": "発行元の機関名（例：防衛省、国土交通省 など略称ではなく正式名称）",
  "license_name": "資格の正式名称（例：競争参加資格、建設工事競争参加資格 など）",
  "trader_code": "業者コードや登録番号（文字列）",
  "valid_from": "有効期間の開始日（YYYY-MM-DD 形式）",
  "valid_to": "有効期間の終了日（YYYY-MM-DD 形式）",
  "grade": "全体としての等級（無い場合は null）",
  "total_score": "全体としての総合点数（無い場合は null）",
  "business_items": [
    {
      "name": "実際に資格が認められている業種・工種名",
      "grade": "その業種での等級。明記がない場合は null",
      "score": "その業種での総合点数・審査数値（数値。無い場合は null）",
      "grade_note": "補足事項がある場合のみ記入、なければ null"
    }
  ],
  "available_regions": []
}
PROMPT;
    }
}