<?php

namespace App\UseCases\Import;

use App\Models\Project;
use Illuminate\Support\Facades\File;

class ProjectCheckAction
{
    private const COLUMN_ID = '案件ID';
    private const COLUMN_NAME = '案件名';
    private const COLUMN_ORG = '機関';

    public function execute(string $path): array
    {
        if (!File::exists($path)) {
            return $this->errorResponse("ファイルが見つかりません。");
        }

        // ファイル全体を読み込み、文字コードを確認・変換する
        $content = file_get_contents($path);
        
        // 文字コード検知（Shift-JIS/CP932を想定）
        $encoding = mb_detect_encoding($content, ['SJIS-win', 'UTF-8', 'eucjp-win', 'SJIS', 'ASCII']);
        
        if ($encoding !== 'UTF-8') {
            // UTF-8に変換
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        // 一時的にメモリ上のストリームとして開く
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        // 1. ヘッダー行の解析
        $rawHeader = fgetcsv($handle);
        if (!$rawHeader) {
            fclose($handle);
            return $this->errorResponse("ファイルの内容が空です。");
        }

        // ヘッダーのクリーニング
        $header = array_map(function($item) {
            $item = preg_replace('/^\xEF\xBB\xBF/', '', $item); // BOM削除
            return trim(str_replace('"', '', $item));
        }, $rawHeader);

        $idIdx = array_search(self::COLUMN_ID, $header);

        if ($idIdx === false) {
            fclose($handle);
            return $this->errorResponse("「" . self::COLUMN_ID . "」列が見つかりません。文字コードを確認してください。");
        }

        $nameIdx = array_search(self::COLUMN_NAME, $header);
        $orgIdx = array_search(self::COLUMN_ORG, $header);

        $results = [
            'new_count' => 0,
            'update_count' => 0,
            'error_count' => 0,
            'details' => []
        ];

        $lineNum = 1;

        // 2. データ行のループ
        while (($data = fgetcsv($handle)) !== FALSE) {
            $lineNum++;

            if (!isset($data[$idIdx]) || trim($data[$idIdx]) === '') {
                continue;
            }

            $externalId = trim($data[$idIdx]);
            $projectName = ($nameIdx !== false && isset($data[$nameIdx])) ? trim($data[$nameIdx]) : '名称不明';
            $organization = ($orgIdx !== false && isset($data[$orgIdx])) ? trim($data[$orgIdx]) : '不明';

            try {
                $exists = Project::where('project_external_id', $externalId)->exists();

                if ($exists) {
                    $results['update_count']++;
                    $results['details'][] = [
                        'line' => $lineNum,
                        'key' => $externalId,
                        'label' => mb_strimwidth($projectName, 0, 40, '...'),
                        'status' => 'update',
                        'message' => "既存更新: {$organization}"
                    ];
                } else {
                    $results['new_count']++;
                    $results['details'][] = [
                        'line' => $lineNum,
                        'key' => $externalId,
                        'label' => mb_strimwidth($projectName, 0, 40, '...'),
                        'status' => 'new',
                        'message' => "新規登録: {$organization}"
                    ];
                }
            } catch (\Exception $e) {
                $results['error_count']++;
                $results['details'][] = [
                    'line' => $lineNum,
                    'key' => $externalId,
                    'label' => $projectName,
                    'status' => 'error',
                    'message' => "エラー: " . $e->getMessage()
                ];
            }
        }

        fclose($handle);
        return $results;
    }

    private function errorResponse(string $message): array
    {
        return [
            'new_count' => 0, 'update_count' => 0, 'error_count' => 1,
            'details' => [[
                'line' => 1, 'key' => 'ERROR', 'label' => 'エラー',
                'status' => 'error', 'message' => $message
            ]]
        ];
    }
}