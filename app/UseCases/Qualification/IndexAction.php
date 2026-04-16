<?php

namespace App\UseCases\Qualification;

use App\Models\Qualification;

class IndexAction
{
    public function __invoke($post)
    {
        $query = Qualification::query();

        // 1. 検索フィルタリング（マイグレーションのカラム名に合わせる）
        if (!empty($post['keyword'])) {
            $keyword = $post['keyword'];
            $query->where(function($q) use ($keyword) {
                $q->where('license_name', 'like', "%{$keyword}%") // 資格名
                  ->orWhere('authority_name', 'like', "%{$keyword}%") // 発行元
                  ->orWhere('trader_code', 'like', "%{$keyword}%"); // 登録番号
            });
        }

        // 2. 並び替え（expired_at ではなく valid_to に修正）
        $query->orderBy('valid_to', 'asc') // 期限が近い順
              ->orderBy('id', 'desc');

        // 3. ページネーション
        $perPage = $post['show_list_cnt'] ?? 10;
        $qualifications = $query->paginate($perPage)->withQueryString();

        // 4. フロント（Vue）が期待する名前に詰め替え
        $qualifications->getCollection()->transform(function ($q) {
            return [
                'id'         => $q->id,
                'name'       => $q->license_name,   // license_name -> name
                'authority'  => $q->authority_name, // authority_name -> authority
                'trader_code'=> $q->trader_code,
                'expired_at' => $q->valid_to ? $q->valid_to->format('Y/m/d') : '-', // valid_to -> expired_at
                'business_items' => $q->business_items, // JSONデータ
                // ★ PDF表示用のURLを追加（ルート名は次で作ります）
                'pdf_url'     => $q->pdf_path ? route('qualification.view-pdf', ['qualification' => $q->id]) : null,
            ];
        });

        $extraData = [
            'filters' => [
                'keyword' => $post['keyword'] ?? '',
            ]
        ];

        return [$qualifications, $extraData];
    }
}