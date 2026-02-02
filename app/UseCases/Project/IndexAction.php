<?php

namespace App\UseCases\Project;

use App\Models\Project;

class IndexAction
{
    public function __invoke($post)
    {
        $query = Project::query();

        // 1. 参画企業の件数をカウント
        $query->withCount('matchedBizs');

        // 2. 検索フィルタリング
        // キーワード検索（案件名 or 組織名）
        if (!empty($post['keyword'])) {
            $keyword = $post['keyword'];
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('organization', 'like', "%{$keyword}%");
            });
        }

        // ステータス検索（0も有効な値なためisset等で判定）
        if (isset($post['status']) && $post['status'] !== '') {
            $query->where('status', $post['status']);
        }

        // 3. 並び替え (入札日が古い順、かつnullは最後にするなどの調整)
        $query->orderByRaw('bid_date IS NULL ASC')
              ->orderBy('bid_date', 'asc');

        // 4. ページネーション
        $perPage = $post['show_list_cnt'] ?? 10;
        
        // 検索条件を保持した状態でリンクを生成
        $projects = $query->paginate($perPage)->withQueryString();

        $extraData = [
            'total_count' => Project::count(),
        ]; 

        return [$projects, $extraData];
    }
}