<?php

namespace App\UseCases\Project;

use App\Models\Project;

class IndexAction
{
    public function __invoke($post)
    {
        // 1. クエリの初期化
        $query = Project::query();

        // 4. ページネーション (数万件あってもOK)
        $perPage = $post['show_list_cnt'] ?? 10;
        $projects = $query->paginate($perPage);
        $extraData = []; 

        return [$projects, $extraData];
    }

}
