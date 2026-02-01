<?php

namespace App\UseCases\Biz;

use App\Models\Biz;

class FormAction
{
    public function __invoke($id)
    {
        if ($id === 0) {
            return new Biz(); // 新規作成時は空
        }

        return Biz::with([
            'scores',          // ★ここを追加：全工種のスコア（スコアカード用）
            'latestScore',     // サマリー用
            'financial', 
            'matches.project' 
        ])->findOrFail($id);
    }
}