<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Biz extends Model
{
    protected $guarded = ['id'];

    // --- 画面表示に必要なリレーション（これらが抜けていたためエラーが出ていました） ---

    public function latestScore() {
        return $this->hasOne(BizScore::class, 'biz_id')->latestOfMany();
    }

    public function matches() {
        return $this->hasMany(ProjectMatch::class, 'biz_id');
    }

    public function scores() {
        return $this->hasMany(BizScore::class, 'biz_id');
    }

    // hasOne の第2引数に外部キー 'biz_id' を明示しておくと安心です
    public function financial() {
        return $this->hasOne(BizFinancial::class, 'biz_id');
    }

    public function comments() {
        return $this->hasMany(BizComment::class);
    }

    public function projects() {
        return $this->belongsToMany(Project::class, 'matches', 'biz_id', 'project_id')
                    ->withPivot(['role', 'status'])
                    ->withTimestamps();
    }

    // --- ID処理ロジック ---

    public static function normalizeId($raw) {
        $clean = trim(str_replace(' ', '', (string)$raw));
        if (empty($clean) || $clean === '手動登録') return null;

        $parts = explode('-', $clean);
        $pref = str_pad($parts[0] ?? '00', 2, '0', STR_PAD_LEFT);
        $num = isset($parts[1]) ? preg_replace('/[^0-9]/', '', $parts[1]) : '0';
        return sprintf('%02s%1d%08d', $pref, 1, $num);
    }

    public static function generateManualId() {
        $max = self::where('permit_id', 'like', '99%')->max('permit_id');
        $next = $max ? (int)substr($max, 2) + 1 : 1;
        return '99' . str_pad($next, 9, '0', STR_PAD_LEFT);
    }
}