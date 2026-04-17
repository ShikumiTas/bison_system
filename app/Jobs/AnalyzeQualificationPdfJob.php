<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Project;
use App\Models\Qualification;
use App\Services\CityResolverService;
use App\Services\AiGeocodingService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyzeQualificationPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;

    /**
     * Create a new job instance.
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * 業種エイリアスの判定ロジック
     */
    private function getIndustryAliases(string $myIndustry): array
    {
        $myIndustry = preg_replace('/[0-9]+位/', '', $myIndustry);
        $myIndustry = trim($myIndustry);

        $map = [
            '内装仕上' => ['内装', '建築工事', '模様替', '改修', '仕上げ', '家具'],
            '建築工事' => ['建築', '模様替', '改修', '修繕'],
            '物品の販売'   => ['物品', '什器', '事務用品', '家具', '備品'],
            '役務の提供等' => ['役務', '清掃', '保守', '点検', '業務委託', '維持管理', '修繕'],
            '冷暖房' => ['空調', '冷暖房', '設備', '保守', '点検', '修理', '修繕'],
            '衛生'   => ['水道', '消防', '設備', '配管'],
            'その他' => ['その他', '建築工事', '土木工事', '改修', '修繕'],
        ];

        if (isset($map[$myIndustry])) return $map[$myIndustry];
        foreach ($map as $key => $aliases) {
            if (mb_strpos($myIndustry, $key) !== false) return $aliases;
        }
        return [$myIndustry];
    }

    /**
     * メイン処理
     */
    public function handle(CityResolverService $cityResolver, AiGeocodingService $aiService)
    {
        $path = $this->path;
        if (!file_exists($path)) {
            Log::error("Import file not found: {$path}");
            return;
        }

        set_time_limit(0);
        ini_set('memory_limit', '1G');

        $qualifications = Qualification::all();
        $myValidTo = $qualifications->max('valid_to')?->format('Y-m-d');
        
        $manualActionedProjects = Project::where('is_target', '>=', 10)
            ->pluck('is_target', 'project_external_id')->toArray();

        // CSV読み込み（SJIS対策）
        $content = mb_convert_encoding(file_get_contents($path), 'UTF-8', 'SJIS-win, CP932, UTF-8');
        $file = fopen('php://temp', 'r+');
        fwrite($file, $content);
        rewind($file);
        fgetcsv($file); // ヘッダーをスキップ

        $data = [];
        while (($row = fgetcsv($file)) !== FALSE) {
            $externalId = $row[1] ?? null;
            if (!$externalId) continue;

            $cityCode = $cityResolver->resolve($row[7] ?? '');
            $finalIsTarget = $manualActionedProjects[$externalId] ?? 0;

            if ($finalIsTarget < 10 && $myValidTo) {
                $csvQuals = $row[12] ?? '';
                $csvIndustry = $row[13] ?? '';
                $isCertMatched = false;

                foreach ($qualifications as $q) {
                    $cleanAuth = str_replace(['独立行政法人', '国立大学法人'], '', $q->authority_name);
                    
                    if (mb_strpos($csvQuals, $cleanAuth) !== false) {
                        $items = is_array($q->business_items) ? $q->business_items : json_decode($q->business_items ?? '[]', true);
                        if ($items) {
                            foreach ($items as $item) {
                                $aliases = $this->getIndustryAliases($item['name'] ?? '');
                                foreach ($aliases as $alias) {
                                    if (mb_strpos($csvIndustry, $alias) !== false || mb_strpos($alias, $csvIndustry) !== false) {
                                        $isCertMatched = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }
                
                $noticeDate = $this->formatDate($row[8] ?? null);
                if ($isCertMatched && $noticeDate && $noticeDate <= $myValidTo) {
                    $finalIsTarget = 1;
                }
            }

            $data[] = [
                'project_external_id'    => $externalId,
                'bidding_type'           => $row[2] ?? null,
                'title'                  => $row[3] ?? null,
                'url'                    => $row[4] ?? null,
                'organization'           => $row[5] ?? null,
                'organization_address'   => $row[6] ?? null,
                'delivery_location'      => $row[7] ?? null,
                'city_cd'                => $cityCode,
                'notice_date'            => $this->formatDate($row[8] ?? null),
                'bid_date'               => $this->formatDate($row[9] ?? null),
                'bidding_qualifications' => $row[12] ?? null,
                'industry'               => $row[13] ?? null,
                'description'            => $row[14] ?? null, // 元のCSVカラムに合わせて調整
                'notes'                  => $row[15] ?? null,
                'winner_name'            => (isset($row[19]) && str_contains($row[19], '有料プラン')) ? null : ($row[19] ?? null),
                'winner_address'         => $row[20] ?? null,
                'is_target'              => $finalIsTarget,
                'updated_at'             => now(),
                'created_at'             => now(),
            ];

            if (count($data) >= 500) {
                $this->performUpsert($data);
                $data = [];
            }
        }

        if (!empty($data)) $this->performUpsert($data);
        fclose($file);
        
        Log::info("Import completed successfully for: {$path}");
    }

    private function performUpsert(array $data) {
        Project::upsert($data, ['project_external_id'], [
            'bidding_type', 'title', 'url', 'organization', 'organization_address',
            'delivery_location', 'city_cd', 'notice_date', 'bid_date', 
            'bidding_qualifications', 'industry', 'description', 'notes', 
            'winner_name', 'winner_address', 'is_target', 'updated_at'
        ]);
    }

    private function formatDate($dateStr) {
        if (!$dateStr || $dateStr === '-' || str_contains($dateStr, '未定')) return null;
        try { return Carbon::parse($dateStr)->format('Y-m-d'); } catch (\Exception $e) { return null; }
    }
}