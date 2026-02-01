<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model {
    // ID以外は一括代入を許可（アジャイル設定）
    protected $guarded = ['id'];

    // 日付カラムを自動的にCarbonオブジェクトへ変換
    protected $casts = [
        'notice_date' => 'date',
        'bid_date' => 'date',
        'briefing_date' => 'date',
        'document_deadline' => 'date',
    ];

    /**
     * 協力会社・関連企業との紐付け
     */
    public function matchedBizs(): BelongsToMany
    {
        // 第2引数に中間テーブル名 'matches' を指定
        return $this->belongsToMany(Biz::class, 'matches', 'project_id', 'biz_id')
                    ->withPivot(['id', 'role', 'status']) // 中間テーブルの列も取得
                    ->withTimestamps();
    }
}