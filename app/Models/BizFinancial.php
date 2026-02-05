<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BizFinancial extends Model {
    protected $guarded = ['id'];

    // 配列を自動でJSON文字列に変換する設定
    protected $casts = [
        'social_details' => 'array',
        'raw_snapshot'   => 'array',
    ];

    public function biz() { return $this->belongsTo(Biz::class); }
}