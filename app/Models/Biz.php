<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Biz extends Model {
    protected $guarded = ['id'];

    // リレーション
    public function scores()
    {
        return $this->hasMany(BizScore::class, 'biz_id');
    }
    public function financial() { return $this->hasOne(BizFinancial::class); }
    public function comments() { return $this->hasMany(BizComment::class); }

    /**
     * 最新のスコアを取得
     */
    public function latestScore() {
        return $this->hasOne(BizScore::class)->latestOfMany();
    }

    /**
     * マッチング（案件実績）
     */
    public function matches() {
        // ProjectMatchモデルを指すように変更
        return $this->hasMany(ProjectMatch::class, 'biz_id');
    }

    /**
     * 参画案件（直通）
     */
    public function projects() {
        return $this->belongsToMany(Project::class, 'matches', 'biz_id', 'project_id')
                    ->withPivot(['role', 'status'])
                    ->withTimestamps();
    }    
    /**
     * 許可番号の正規化 (大臣 00-000135 -> 00100000135)
     * $pref: '00', '23' / $type: 1(般), 2(特) / $num: '135'
     */
    public static function normalizeId($pref, $type, $num) {
        $cleanNum = preg_replace('/[^0-9]/', '', $num);
        return sprintf('%02s%1d%08d', $pref, $type, $cleanNum);
    }

    /**
     * 手動登録用IDの発行 (99 + 連番)
     */
    public static function generateManualId() {
        $max = self::where('permit_id', 'like', '99%')->max('permit_id');
        $next = $max ? (int)substr($max, 2) + 1 : 1;
        return '99' . str_pad($next, 9, '0', STR_PAD_LEFT);
    }
}