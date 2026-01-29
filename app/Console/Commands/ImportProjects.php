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

    dd($path);
        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return;
        }

        $file = fopen($path, 'r');
        $header = fgetcsv($file); // 1行目（ヘッダー）を飛ばす

        $data = [];
        $count = 0;

        while (($row = fgetcsv($file)) !== FALSE) {
            // CSVのカラムとDBの値をマッピング（例）
            $data[] = [
                'project_code' => $row[0], // A列
                'name'         => $row[1], // B列
                'status'       => $row[2], // C列
                'updated_at'   => now(),
                'created_at'   => now(),
            ];

            // 1000件ごとに一括保存（メモリ節約 & 高速化）
            if (count($data) >= 1000) {
                $this->upsertData($data);
                $data = [];
            }
            $count++;
        }

        // 残りのデータを保存
        if (!empty($data)) {
            $this->upsertData($data);
        }

        fclose($file);
        
        // 処理が終わったらファイルを消す（ストレージ節約）
        unlink($path);

        $this->info("Successfully imported {$count} projects.");
    }

    private function upsertData(array $data)
    {
        // project_code が重複していたら更新、なければ挿入
        Project::upsert($data, ['project_code'], ['name', 'status', 'updated_at']);
    }
}