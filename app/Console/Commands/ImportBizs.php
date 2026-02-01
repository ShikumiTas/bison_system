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
    protected $description = '企業経審CSVデータをインポートします';

    // 工種名と「許可区分」列のインデックス対応
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
        $path = $this->argument('path');
        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return;
        }

        $this->info("インポートを開始します...");
        set_time_limit(0); 
        ini_set('memory_limit', '1G'); 

        $content = file_get_contents($path);
        $content = mb_convert_encoding($content, 'UTF-8', 'ASCII,JIS,UTF-8,CP932,SJIS-win');
        $file = fopen('php://temp', 'r+');
        fwrite($file, $content);
        rewind($file);

        fgetcsv($file); // ヘッダースキップ

        $count = 0;
        DB::beginTransaction();

        try {
            while (($row = fgetcsv($file)) !== FALSE) {
                if (empty($row[4])) continue;

                $permitId = $this->generatePermitId($row[4]);

                // 1. bizs (基本情報)
                $biz = Biz::updateOrCreate(
                    ['permit_id' => $permitId],
                    [
                        'company_name'      => $row[2],
                        'representative_name'=> $row[3],
                        'permit_number_raw' => $row[4],
                        'zip_code'          => $row[0],
                        'address'           => $row[1],
                        'phone_number'      => $row[6],
                        'city_code'         => $row[7],
                        'capital'           => $this->toNumeric($row[8]) * 1000,
                        'sales_ratio'       => (float)($row[9] ?? 0),
                        'admin_section'     => $row[10] ?? null,
                        'review_base_date'  => $this->parseDate($row[5]),
                        'is_manual'         => false,
                    ]
                );

                // 2. biz_scores (工種別スコア + 詳細JSON)
                foreach ($this->categories as $name => $idx) {
                    $permitType = $row[$idx] ?? null;
                    if (!$permitType || $permitType === '無') continue;

                    // 12項目セット内の詳細データを共通キーで抽出
                    $details = [
                        '評点X1'      => $row[$idx + 3] ?? null,
                        '元請完工高'   => $this->toNumeric($row[$idx + 4] ?? 0) * 1000,
                        '一級技術者'   => $row[$idx + 5] ?? '0',
                        '講習受講'     => $row[$idx + 6] ?? '0',
                        '監理技術者補佐'=> $row[$idx + 7] ?? '0',
                        '基幹技能者'   => $row[$idx + 8] ?? '0',
                        '二級技術者'   => $row[$idx + 9] ?? '0',
                        'その他技術者' => $row[$idx + 10] ?? '0',
                        '評点Z'       => $row[$idx + 11] ?? null,
                    ];

                    BizScore::updateOrCreate(
                        ['biz_id' => $biz->id, 'work_category' => $name],
                        [
                            'permit_type' => $permitType,
                            'p_score'     => (int)($row[$idx + 1] ?? 0),
                            'avg_sales'   => $this->toNumeric($row[$idx + 2] ?? 0) * 1000,
                            'details'     => $details, // JSONとして保存
                        ]
                    );
                }

                BizFinancial::updateOrCreate(['biz_id' => $biz->id], []);
                $count++;
            }
            DB::commit();
            $this->info("成功: {$count} 件の企業データをインポートしました。");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("エラー発生: " . $e->getMessage());
        }

        fclose($file);
    }

    private function generatePermitId($raw) {
        $parts = explode('-', str_replace(' ', '', $raw));
        $pref = str_pad($parts[0] ?? '00', 2, '0', STR_PAD_LEFT);
        $num  = isset($parts[1]) ? (int)$parts[1] : 0;
        return sprintf('%02s%1d%08d', $pref, 1, $num);
    }

    private function toNumeric($val) {
        if (is_numeric($val)) return $val;
        $clean = str_replace(',', '', $val);
        return is_numeric($clean) ? (int)$clean : 0;
    }

    private function parseDate($val) {
        if (!$val) return null;
        try { return Carbon::parse($val)->format('Y-m-d'); } 
        catch (\Exception $e) { return null; }
    }
}