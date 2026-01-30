<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ImportProjects extends Command
{
    // path 引数を受け取れるように定義
    protected $signature = 'import:projects {path}';

    public function handle()
    {
        $path = $this->argument('path');
        \Log::info("CSVインポート開始: {$path}");

        if (!file_exists($path)) return;

        // 文字コード対策をしてストリームを開く
        $content = file_get_contents($path);
        $content = mb_convert_encoding($content, 'UTF-8', 'ASCII,JIS,UTF-8,CP932,SJIS-win');
        $file = fopen('php://temp', 'r+');
        fwrite($file, $content);
        rewind($file);

        fgetcsv($file); // ヘッダーをスキップ

        $chunkSize = 1000;
        $data = [];
        $count = 0;

        while (($row = fgetcsv($file)) !== FALSE) {
            if (empty(array_filter($row))) continue;

            // DBのカラム名 => CSVのインデックス
            // ImportProjects.php の handle内マッピング部分

            $data[] = [
                'project_external_id' => $row[1],  // 案件ID
                'title'               => $row[3],  // 案件名
                'bidding_type'        => $row[2],  // 入札形式
                'url'                 => $row[4],  // 案件概要URL
                'organization'        => $row[5],  // 機関名
                'bid_date'            => $this->formatDate($row[9]), // 入札日
                
                // 【重要】もし今後カラムを追加するならここが住所
                // 'organization_address' => $row[6], // 機関所在地
                // 'winner_address'       => $row[20], // 落札会社住所
                
                'winning_price'       => is_numeric($row[22]) ? (int)$row[22] : null,
                'winner_name'         => ($row[19] && str_contains($row[19], '有料プラン')) ? null : $row[19],
                
                'updated_at'          => now(),
                'created_at'          => now(),
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
        unlink($path);

        \Log::info("インポート完了: 合計 {$count} 件");
    }

    /**
     * データベースへ一括保存 (重複時は更新)
     */
    private function performUpsert(array $data)
    {
        \App\Models\Project::upsert(
            $data, 
            ['project_external_id'], // 重複判定に使うユニークキー
            ['title', 'bidding_type', 'url', 'organization', 'bid_date', 'winning_price', 'winner_name', 'updated_at'] // 更新するカラム
        );
    }
    /**
     * 日付形式を yyyy-mm-dd に整える
     */
    private function formatDate($dateStr)
    {
        if (!$dateStr || $dateStr === '-') return null;
        try {
            return \Carbon\Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function upsertData(array $data)
    {
        // project_code が重複していたら更新、なければ挿入
        Project::upsert($data, ['project_code'], ['name', 'status', 'updated_at']);
    }
}