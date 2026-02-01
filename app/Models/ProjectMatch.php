<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMatch extends Model
{
    // テーブル名は変えなくてOK
    protected $table = 'matches';
    protected $guarded = ['id'];

    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function biz() {
        return $this->belongsTo(Biz::class);
    }
}