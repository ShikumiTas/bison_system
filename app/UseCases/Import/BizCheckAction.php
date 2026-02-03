<?php

namespace App\UseCases\Import;

use App\Models\Biz;
use Illuminate\Support\Facades\File;

class BizCheckAction
{
    // 経審CSVのヘッダー名定義
    private const COLUMN_ID = '許可番号'; // これを識別キーにする
    private const COLUMN_NAME = '社名';
    private const COLUMN_ADDR = '所在地';

    public function execute(string $path): array
    {
        if (!File::exists($path)) {
            return $this->errorResponse("ファイルが見つかりません。");
        }

        // 1. 文字コード変換（Shift-JIS -> UTF-8）
        $content = file_get_contents($path);
        $encoding = mb_detect_encoding($content, ['SJIS-win', 'UTF-8', 'eucjp-win', 'SJIS', 'ASCII']);
        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        // 2. ヘッダー解析
        $rawHeader = fgetcsv($handle);
        if (!$rawHeader) {
            fclose($handle);
            return $this->errorResponse("ファイルの内容が空です。");
        }

        $header = array_map(function($item) {
            $item = preg_replace('/^\xEF\xBB\xBF/', '', $item);
            return trim(str_replace('"', '', $item));
        }, $rawHeader);

        $idIdx = array_search(self::COLUMN_ID, $header);
        $nameIdx = array_search(self::COLUMN_NAME, $header);
        $addrIdx = array_search(self::COLUMN_ADDR, $header);

        if ($idIdx === false) {
            fclose($handle);
            return $this->errorResponse("「" . self::COLUMN_ID . "」列が見つかりません。経審データを選択してください。");
        }

        $results = [
            'new_count' => 0,
            'update_count' => 0,
            'error_count' => 0,
            'details' => []
        ];

        $lineNum = 1;

        // 3. データ行のループ
        while (($data = fgetcsv($handle)) !== FALSE) {
            $lineNum++;

            // 許可番号が空ならスキップ
            if (!isset($data[$idIdx]) || trim($data[$idIdx]) === '') {
                continue;
            }

            $bizNumber = trim($data[$idIdx]); // 許可番号
            $bizName = ($nameIdx !== false) ? trim($data[$nameIdx] ?? '不明') : '不明';
            $bizAddr = ($addrIdx !== false) ? trim($data[$addrIdx] ?? '') : '';

            try {
                // DB照合（biz_number または許可番号カラムで検索）
                // ※実際のテーブルのカラム名に合わせて調整してください
                $exists = Biz::where('biz_number', $bizNumber)->exists();

                if ($exists) {
                    $results['update_count']++;
                    $results['details'][] = [
                        'line' => $lineNum,
                        'key' => $bizNumber,
                        'label' => $bizName,
                        'status' => 'update',
                        'message' => "既存更新: {$bizAddr}"
                    ];
                } else {
                    $results['new_count']++;
                    $results['details'][] = [
                        'line' => $lineNum,
                        'key' => $bizNumber,
                        'label' => $bizName,
                        'status' => 'new',
                        'message' => "新規登録: {$bizAddr}"
                    ];
                }
            } catch (\Exception $e) {
                $results['error_count']++;
                $results['details'][] = [
                    'line' => $lineNum,
                    'key' => $bizNumber,
                    'label' => $bizName,
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