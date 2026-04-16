<?php

namespace App\UseCases\Import;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class CheckAction
{
    private const HEADERS = [
        'project' => '案件ID',
        'biz'     => '許可番号'
    ];

    // 手動登録時に必須となるバックアップ列（許可番号が空でもこの列があればカウントする）
    private const REQUIRED_SUB = [
        'project' => '案件名', // プロジェクトの場合は案件名
        'biz'     => '社名'     // 企業の場合は社名
    ];

    public function execute(string $tempPath, string $type): array
    {
        $fullPath = Storage::disk('local')->path($tempPath);

        if (!File::exists($fullPath)) {
            return $this->errorResponse("サーバー上にファイルが保存されていません。再送してください。");
        }

        // ★ PDF（資格情報）の場合は、CSV解析をスキップしてファイルチェックのみ行う
        if ($type === 'qualification') {
            return $this->checkPdfFile($fullPath);
        }

        // --- 以下、従来のCSV解析ロジック ---
        $content = File::get($fullPath);
        $encoding = mb_detect_encoding($content, ['SJIS-win', 'UTF-8', 'SJIS', 'ASCII']);
        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        $rawHeader = fgetcsv($handle);
        if (!$rawHeader) {
            fclose($handle);
            return $this->errorResponse("ファイルが空です。");
        }

        $header = array_map(function($item) {
            $item = preg_replace('/^\xEF\xBB\xBF/', '', $item);
            return trim(str_replace('"', '', $item));
        }, $rawHeader);

        // 判定基準となる列のインデックスを取得
        $targetColumn = self::HEADERS[$type] ?? null;
        $subColumn    = self::REQUIRED_SUB[$type] ?? null;

        $idIdx  = array_search($targetColumn, $header);
        $subIdx = array_search($subColumn, $header);

        // 許可番号の列すら見つからない場合はエラー
        if ($idIdx === false) {
            fclose($handle);
            return $this->errorResponse("CSVに「{$targetColumn}」列が見つかりません。");
        }

        $totalCount = 0;
        $errors = [];
        $lineNum = 1; // ヘッダー分

        // 4. 改良版カウントロジック
        while (($data = fgetcsv($handle)) !== FALSE) {
            $lineNum++;
            
            // 全列空（空行）は無視
            if (empty(array_filter($data))) continue;

            $idValue  = trim($data[$idIdx] ?? '');
            $subValue = ($subIdx !== false) ? trim($data[$subIdx] ?? '') : '';

            // 【判定ロジックの修正】
            // 許可番号がある ＝ 通常インポート
            // 許可番号が空 ＋ 社名がある ＝ 手動登録としてカウント
            if ($idValue !== '' || $subValue !== '') {
                $totalCount++;
            } else {
                // 両方空の場合はエラー（何もない行）
                $errors[] = [
                    'line' => $lineNum,
                    'status' => 'error',
                    'message' => "{$targetColumn} または {$subColumn} が入力されていません。"
                ];
            }
        }

        fclose($handle);

        return [
            'total_count' => $totalCount,
            'error_count' => count($errors),
            'details'     => $errors
        ];
    }

    /**
     * ★ PDFファイルの整合性を最小限チェックする
     */
    private function checkPdfFile(string $fullPath): array
    {
        // 1. 念のためファイルサイズが0でないか確認
        if (File::size($fullPath) === 0) {
            return $this->errorResponse("PDFファイルが空です。");
        }

        // 2. ファイルのヘッダー（マジックバイト）がPDF形式（%PDF）になっているか確認
        $handle = fopen($fullPath, 'rb');
        $header = fread($handle, 4);
        fclose($handle);

        if ($header !== '%PDF') {
            return $this->errorResponse("ファイル形式が不正です。有効なPDFファイルをアップロードしてください。");
        }

        // チェックを通過した場合は、処理対象のPDFが「1件」あるものとして返す
        return [
            'total_count' => 1,
            'error_count' => 0,
            'details'     => []
        ];
    }

    private function errorResponse(string $message): array
    {
        return [
            'total_count' => 0,
            'error_count' => 1,
            'details' => [['line' => 1, 'status' => 'error', 'message' => $message]]
        ];
    }
}