<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Biz;
use App\Models\BizScore;
use App\Models\BizFinancial;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportBizs extends Command
{
    protected $signature = 'import:bizs {path}';
    protected $description = 'Book2.csvの列固定に対応しつつ、全ての経審スコアを保存します';

    // 以前のソースでうまくいっていた工種別インデックス（12項目セット）
    private $categories = [
        '土木一式' => 11, 'プレストレスト' => 23, '建築一式' => 35, '大工' => 47,
        '左官' => 59, 'とび・土工' => 71, '石' => 83, '屋根' => 95,
        '電気' => 107, '管' => 119, 'タイル・れんが' => 131, '鋼構造物' => 143,
        '鉄筋' => 155, '舗装' => 167, 'しゅんせつ' => 179, '板金' => 191,
        'ガラス' => 203, '塗装' => 215, '防水' => 227, '内装仕上' => 239,
        '機械器具設置' => 251, '熱絶縁' => 263, '電気通信' => 275, '造園' => 287,
        'さく井' => 299, '建具' => 311, '水道施設' => 323, '消防施設' => 335,
        '清掃' => 347, '解体' => 359
    ];

    public function handle()
    {
        $inputPath = $this->argument('path');
        $path = $this->resolvePath($inputPath);

        if (!$path) {
            $this->error("File not found: {$inputPath}");
            return;
        }

        $this->info("Importing: {$path}");
        set_time_limit(0);
        ini_set('memory_limit', '1G');

        $content = file_get_contents($path);
        $content = mb_convert_encoding($content, 'UTF-8', 'ASCII,JIS,UTF-8,CP932,SJIS-win');
        $file = fopen('php://temp', 'r+');
        fwrite($file, $content);
        rewind($file);

        fgetcsv($file); // ヘッダー

        $count = 0;
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== FALSE) {
                $name = trim($row[2] ?? '');
                if (empty($name)) continue;

                $rawPermit = trim($row[4] ?? '');
                
                // Biz.phpに定義した静的メソッドを呼び出し
                $permitId = Biz::normalizeId($rawPermit) ?: Biz::generateManualId();
                $isManual = empty($rawPermit);

                // 1. Biz基本情報
                $biz = Biz::updateOrCreate(
                    ['permit_id' => $permitId],
                    [
                        'company_name'       => $name,
                        'representative_name'=> $row[3] ?? null,
                        'permit_number_raw'  => $rawPermit ?: '手動登録',
                        'zip_code'           => $row[0] ?? null,
                        'address'            => $row[1] ?? null,
                        'phone_number'       => $row[6] ?? null,
                        'city_code'          => $row[7] ?? null,
                        'capital'            => $this->toNumeric($row[8] ?? 0) * 1000,
                        'sales_ratio'        => (float)($row[9] ?? 0),
                        'admin_section'      => $row[10] ?? null,
                        'review_base_date'   => $this->parseDate($row[5] ?? null),
                        'is_manual'          => $isManual,
                    ]
                );

                // 2. 工種別スコア (biz_scores)
                foreach ($this->categories as $catName => $idx) {
                    $permitType = $row[$idx] ?? null;
                    if (!$permitType || $permitType === '無') continue;

                    $details = [
                        '評点X1'       => $row[$idx + 3] ?? null,
                        '元請完工高'    => $this->toNumeric($row[$idx + 4] ?? 0) * 1000,
                        '一級技術者'    => $row[$idx + 5] ?? '0',
                        '講習受講'      => $row[$idx + 6] ?? '0',
                        '監理技術者補佐' => $row[$idx + 7] ?? '0',
                        '基幹技能者'    => $row[$idx + 8] ?? '0',
                        '二級技術者'    => $row[$idx + 9] ?? '0',
                        'その他技術者'  => $row[$idx + 10] ?? '0',
                        '評点Z'        => $row[$idx + 11] ?? null,
                    ];

                    BizScore::updateOrCreate(
                        ['biz_id' => $biz->id, 'work_category' => $catName],
                        [
                            'permit_type' => $permitType,
                            'p_score'     => (int)($row[$idx + 1] ?? 0),
                            'avg_sales'   => $this->toNumeric($row[$idx + 2] ?? 0) * 1000,
                            'details'     => $details,
                            'review_base_date' => $biz->review_base_date,
                        ]
                    );
                }

                BizFinancial::updateOrCreate(['biz_id' => $biz->id], []);
                $count++;
            }
            DB::commit();
            $this->info("Successfully imported {$count} items with full scores.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
        }
    }

    private function resolvePath($input) {
        $paths = [$input, storage_path('app/private/'.$input), storage_path('app/'.$input)];
        foreach ($paths as $p) { if (file_exists($p)) return $p; }
        return null;
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