<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BizScore extends Model {
    protected $guarded = ['id'];
    public function biz() { return $this->belongsTo(Biz::class); }
}