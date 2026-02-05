<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model {

    const STATUS_READY = 0;      // 準備中
    const STATUS_IN_PROGRESS = 1; // 進行中
    const STATUS_COMPLETED = 2;   // 完工
    const STATUS_CANCELLED = 9;   // 失注

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
        return $this->belongsToMany(Biz::class, 'matches', 'project_id', 'biz_id')
                    ->withPivot(['id', 'role', 'status', 'memo']) // 'memo' があるなら追加
                    ->withTimestamps();
    }

    // /**
    //  * 案件ステータスの更新
    //  */

    // public function updateStatusAutomatically()
    // {
    //     $bizCount = $this->matchedBizs()->count();
    //     $completedCount = $this->matchedBizs()->where('status', 'completed')->count();

    //     if ($bizCount === 0) {
    //         $newStatus = 1; // 準備中
    //     } elseif ($bizCount > 0 && $bizCount > $completedCount) {
    //         $newStatus = 2; // 進行中
    //     } elseif ($bizCount > 0 && $bizCount === $completedCount) {
    //         $newStatus = 3; // 失注
    //     }

    //     $this->update(['status_id' => $newStatus]);
    // }
}