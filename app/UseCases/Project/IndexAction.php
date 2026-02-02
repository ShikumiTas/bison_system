<?php

namespace App\UseCases\Project;

use App\Models\Project;

class IndexAction
{
    public function __invoke($post)
    {
        $query = Project::query();

        // 1. 参画企業の件数をカウント (matched_bizs_count というカラムが自動追加される)
        $query->withCount('matchedBizs');

        // 2. 入札日の昇順で並び替え (nullは最後に持ってくるなどの工夫も可能)
        // ここではシンプルに期限が近いものから表示
        $query->orderBy('bid_date', 'asc');

        // 3. ページネーション
        $perPage = $post['show_list_cnt'] ?? 10;
        $projects = $query->paginate($perPage);

        $extraData = [
            'total_count' => Project::count(), // 全体の案件数
            // 必要に応じて「今週入札の案件数」などもここに入れられます
        ]; 

        return [$projects, $extraData];
    }
}