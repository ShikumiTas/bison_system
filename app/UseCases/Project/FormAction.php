<?php

namespace App\UseCases\Project;

use App\Models\Project;

class FormAction
{
    public function __invoke($project_id = 0)
    {
        // IDが0なら空のインスタンス、そうでなければDBから取得
        $project = $project_id == 0 
            ? new Project() 
            : Project::findOrFail($project_id);

        // Eloquentのリレーションを使って紐付け企業をロード
        if ($project->exists) {
            $project->load('matchedBizs');
        }

        return [
            $project,
            $project->matchedBizs ?? [] // これで Eloquent コレクションとして返る
        ];
    }
}
