<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BizFinancial extends Model {
    protected $guarded = ['id'];
    public function biz() { return $this->belongsTo(Biz::class); }
}