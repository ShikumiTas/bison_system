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
        if (!empty($post['keyword'])) {
            $keyword = $post['keyword'];
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('organization', 'like', "%{$keyword}%");
            });
        }

        if (isset($post['status']) && $post['status'] !== '') {
            $query->where('status', $post['status']);
        }

       
        // --- 修正箇所：is_target の判定 ---

        // フロントから送られてきた値を確認
        $targetParam = $post['is_target'] ?? null;

        if ($targetParam === null) {
            // 1. URLにパラメータがない場合（初回アクセス時など）
            // 通常(1) と 手動確定(10) をデフォルトで表示
            $query->whereIn('is_target', [1, 10]);
        } 
        elseif ($targetParam == 0) {
            // 2. 「全て」ボタン（value=0）が押された場合
            // where を追加しないことで、20（対象外）を含むすべてのレコードを表示
        } 
        else {
            // 3. 個別の絞り込み（1 や 20 が明示的に指定された場合）
            if ($targetParam == 1) {
                // 「通常」のときは、利便性のために 10 も含める
                $query->whereIn('is_target', [1, 10]);
            } else {
                // 20（対象外）などが選ばれたらその値で絞り込む
                $query->where('is_target', $targetParam);
            }
        }
        // -------------------------------------------------------------

        // 3. 並び替え
        $query->orderByRaw('bid_date IS NULL ASC')
              ->orderBy('bid_date', 'asc');

        // 4. ページネーション
        $perPage = $post['show_list_cnt'] ?? 10;
        
        $projects = $query->paginate($perPage)->withQueryString();

        $extraData = [
            'total_count' => Project::count(),
        ]; 

        return [$projects, $extraData];
    }
}