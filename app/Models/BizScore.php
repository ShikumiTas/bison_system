<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizScore extends Model
{
    protected $guarded = ['id'];

    /**
     * detailsカラムを自動的に配列として扱うための設定
     */
    protected $casts = [
        'details' => 'array',
    ];

    /**
     * 親の企業情報へのリレーション
     */
    public function biz(): BelongsTo
    {
        return $this->belongsTo(Biz::class);
    }
}