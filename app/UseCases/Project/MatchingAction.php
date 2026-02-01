<?php
namespace App\UseCases\Project;

use App\Models\Project;

class MatchingAction
{
    public function __invoke($project_id, array $data)
    {
        $project = Project::findOrFail($project_id);

        // syncWithoutDetaching を使うと、既存の紐付けを壊さずに
        // 新しい企業を追加（またはピボットデータの更新）ができます
        $project->matchedBizs()->syncWithoutDetaching([
            $data['biz_id'] => [
                'role'   => $data['role'] ?? '協力会社',
                'status' => 'requesting',
            ]
        ]);
    }
}