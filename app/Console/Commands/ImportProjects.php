<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportProjects extends Command
{
    protected $signature = 'import:projects {path}';
    protected $description = 'CSVからプロジェクト情報をインポートします';

    public function handle()
    {
        $path = $this->argument('path');
        Log::info("CSVインポート開始: {$path}");

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return;
        }

        set_time_limit(0); 
        ini_set('memory_limit', '1G'); 

        $content = file_get_contents($path);
        $content = mb_convert_encoding($content, 'UTF-8', 'ASCII,JIS,UTF-8,CP932,SJIS-win');
        $file = fopen('php://temp', 'r+');
        fwrite($file, $content);
        rewind($file);

        fgetcsv($file); // ヘッダー行をスキップ

        $chunkSize = 1000;
        $data = [];
        $count = 0;

        while (($row = fgetcsv($file)) !== FALSE) {
            if (!isset($row[1]) || empty($row[1])) continue;

            $data[] = [
                'project_external_id'    => $row[1],                          // 案件ID
                'bidding_type'           => $row[2] ?? null,                  // 入札形式
                'title'                  => $row[3] ?? null,                  // 案件名
                'url'                    => $row[4] ?? null,                  // 案件概要URL
                'organization'           => $row[5] ?? null,                  // 機関名
                'organization_address'   => $row[6] ?? null,                  // 機関所在地
                'delivery_location'      => $row[7] ?? null,                  // 履行/納品場所
                'notice_date'            => $this->formatDate($row[8] ?? null), // 案件公示日
                'bid_date'               => $this->formatDate($row[9] ?? null), // 入札日
                
                // 追加項目（想定されるCSV列番号に合わせて調整してください）
                'bidding_qualifications' => $row[13] ?? null,                 // 入札資格
                'industry'               => $row[14] ?? null,                 // 業種
                'description'            => $row[15] ?? null,                 // 案件概要
                'notes'                  => $row[21] ?? null,                 // 案件備考（特記事項）

                // 落札者情報（19:会社名, 20:住所）
                'winner_name'            => ($row[19] && str_contains($row[19], '有料プラン')) ? null : $row[19],
                'winner_address'         => $row[20] ?? null,
                
                'updated_at'             => now(),
                'created_at'             => now(),
            ];

            if (count($data) >= $chunkSize) {
                $this->performUpsert($data);
                $data = [];
            }
            $count++;
        }

        if (!empty($data)) {
            $this->performUpsert($data);
        }

        fclose($file);
        $this->info("インポート完了: 合計 {$count} 件");
        Log::info("インポート完了: 合計 {$count} 件");
    }

    private function performUpsert(array $data)
    {
        Project::upsert(
            $data, 
            ['project_external_id'],
            [
                'bidding_type', 
                'title', 
                'url', 
                'organization', 
                'organization_address', 
                'delivery_location', 
                'notice_date', 
                'bid_date', 
                'bidding_qualifications', // 更新対象に追加
                'industry',               // 更新対象に追加
                'description',            // 更新対象に追加
                'notes',                  // 更新対象に追加
                'winner_name', 
                'winner_address', 
                'updated_at'
            ]
        );
    }

    private function formatDate($dateStr)
    {
        if (!$dateStr || $dateStr === '-' || str_contains($dateStr, '未定')) return null;
        try {
            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}