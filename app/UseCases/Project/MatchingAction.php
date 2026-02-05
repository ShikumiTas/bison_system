<?php

namespace App\UseCases\Project;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class MatchingAction
{
    /**
     * 案件と企業の紐付け、およびステータス・メモ・役割の更新
     */
    public function __invoke(int $projectId, array $data)
    {
        $project = Project::findOrFail($projectId);
        $bizId = $data['biz_id'];
        
        // 中間テーブル 'matches' のカラムに合わせてセット
        $attributes = [];

        // ステータスの更新
        if (isset($data['status'])) {
            $attributes['status'] = $data['status'];
        }

        // メモの更新 (空文字を保存できるよう array_key_exists を使用)
        if (array_key_exists('memo', $data)) {
            $attributes['memo'] = $data['memo'];
        }

        // --- 役割（role）の追加 ---
        if (array_key_exists('role', $data)) {
            $attributes['role'] = $data['role'];
        }

        // リレーション matchedBizs() を通じて中間テーブルを更新
        // syncWithoutDetaching は既存の紐付けを維持したまま、指定した biz_id の属性のみを更新/追加します
        return $project->matchedBizs()->syncWithoutDetaching([
            $bizId => $attributes
        ]);
    }
}