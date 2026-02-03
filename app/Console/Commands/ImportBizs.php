<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Biz;
use App\Models\BizFinancial;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportBizs extends Command
{
    // 引数 path を必須にします
    protected $signature = 'import:bizs {path}';
    protected $description = 'CSVインポート（パス指定・列固定版）';

    public function handle()
    {
        $inputPath = $this->argument('path');
        $path = null;

        // 指定されたパスを順番に確認（Sail環境のパス解決）
        $locations = [
            $inputPath,
            storage_path('app/private/' . $inputPath),
            storage_path('app/' . $inputPath),
        ];

        foreach ($locations as $loc) {
            if (file_exists($loc)) {
                $path = $loc;
                break;
            }
        }

        if (!$path) {
            $this->error("ファイルが見つかりません: {$inputPath}");
            return;
        }

        $this->info("Importing: {$path}");

        $content = file_get_contents($path);
        $content = mb_convert_encoding($content, 'UTF-8', 'SJIS-win,UTF-8');
        $file = fopen('php://temp', 'r+');
        fwrite($file, $content);
        rewind($file);

        fgetcsv($file); // ヘッダーを1行飛ばす

        $count = 0;
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== FALSE) {
                // Book2.csv の列構成に基づき、インデックスを完全に固定
                $name = trim($row[2] ?? ''); // 3列目: 社名
                if (empty($name)) continue;

                $rawPermit = trim($row[4] ?? ''); // 5列目: 許可番号
                
                // 許可番号があれば正規化、なければ手動ID発行
                $permitId = Biz::normalizeId($rawPermit);
                $isManual = false;
                if (!$permitId) {
                    $permitId = Biz::generateManualId();
                    $isManual = true;
                }

                $biz = Biz::updateOrCreate(
                    ['permit_id' => $permitId],
                    [
                        'company_name'       => $name,
                        'representative_name'=> $row[3] ?? null, // 4列目
                        'permit_number_raw'  => $rawPermit ?: '手動登録',
                        'zip_code'           => $row[0] ?? null, // 1列目
                        'address'            => $row[1] ?? null, // 2列目
                        'review_base_date'   => $this->parseDate($row[5] ?? null), // 6列目
                        'phone_number'       => $row[6] ?? null, // 7列目
                        'city_code'          => $row[7] ?? null, // 8列目
                        'capital'            => $this->toNumeric($row[8] ?? 0), // 9列目
                        'sales_ratio'        => (float)($row[9] ?? 0), // 10列目
                        'is_manual'          => $isManual,
                    ]
                );

                BizFinancial::updateOrCreate(['biz_id' => $biz->id], []);
                $count++;
            }
            DB::commit();
            $this->info("Successfully imported {$count} items.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
        }
    }

    private function toNumeric($val) {
        $clean = str_replace(',', '', (string)$val);
        return is_numeric($clean) ? (int)$clean : 0;
    }

    private function parseDate($val) {
        if (!$val) return null;
        try { return Carbon::parse($val)->format('Y-m-d'); } 
        catch (\Exception $e) { return null; }
    }
}