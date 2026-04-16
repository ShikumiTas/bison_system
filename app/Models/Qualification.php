<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Qualification extends Model
{
    use HasFactory;

    protected $fillable = [
        'authority_name',
        'license_name',
        'trader_code',
        'valid_from',
        'valid_to',
        'business_items',
        'pdf_path',
    ];

    /**
     * ★ここが超重要：JSONカラムを配列として扱うキャスト設定
     */
    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'business_items' => 'array', // JSONを自動でPHPの配列に変換
    ];
}