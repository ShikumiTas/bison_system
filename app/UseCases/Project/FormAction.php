<?php

namespace App\UseCases\Project;

use App\Models\Project;

class FormAction
{

    public function __invoke($project_id = 0)
    {
        $project = $project_id == 0 
            ? new Project() 
            : Project::findOrFail($project_id);

        $relatedBizs = [];

        if ($project->exists) {
            // リレーションをロード
            $project->load('matchedBizs');
            
            // Vue側で biz.status や biz.memo として直接アクセスできるように整形
            $relatedBizs = $project->matchedBizs->map(function ($biz) {
                return array_merge($biz->toArray(), [
                    'status' => $biz->pivot->status,
                    'memo'   => $biz->pivot->memo,
                    'updated_at_human' => $biz->pivot->updated_at?->diffForHumans(), // 更新時間を「1時間前」のように変換
                ]);
            });
        }

        return [
            $project,
            $relatedBizs
        ];
    }
}
