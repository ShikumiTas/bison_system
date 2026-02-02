<?php

namespace App\UseCases\Project;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class MatchingAction
{
    /**
     * 案件と企業の紐付け、およびステータス・メモの更新
     */

    public function __invoke(int $projectId, array $data)
    {
        $project = Project::findOrFail($projectId);
        $bizId = $data['biz_id'];
        
        // 中間テーブル 'matches' のカラムに合わせてセット
        $attributes = [];
        if (isset($data['status'])) {
            $attributes['status'] = $data['status'];
        }

        if (array_key_exists('memo', $data)) {
                $attributes['memo'] = $data['memo'];
        }

        // リレーション名を matchedBizs() に変更
        return $project->matchedBizs()->syncWithoutDetaching([
            $bizId => $attributes
        ]);
    }
}