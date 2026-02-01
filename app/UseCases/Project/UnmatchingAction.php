<?php

namespace App\UseCases\Project;

use App\Models\Project;

class UnmatchingAction
{
    public function __invoke($projectId, $bizId)
    {
        $project = Project::findOrFail($projectId);
        // belongsToMany リレーションを使って中間テーブルから削除
        $project->matchedBizs()->detach($bizId);
    }
}