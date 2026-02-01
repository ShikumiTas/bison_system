<?php

namespace App\UseCases\Biz;

use App\Models\Biz;

class IndexAction
{
    public function __invoke($post)
    {
        $query = Biz::query()
            ->with([
                'projects' => function ($q) {
                    // ここを name から title に修正
                    $q->where('matches.status', 'ongoing')
                      ->select('projects.id', 'projects.title'); 
                }
            ])
            ->withCount([
                'projects as projects_count',
                'projects as requesting_count' => function ($q) {
                    $q->where('matches.status', 'requesting');
                },
                'projects as received_count' => function ($q) {
                    $q->where('matches.status', 'received');
                },
                'projects as ongoing_count' => function ($q) {
                    $q->where('matches.status', 'ongoing');
                }
            ]);

        // --- 中略（検索ロジックなどはそのまま） ---
        
        $status = $post['status'] ?? 'active';
        if ($status === 'active') {
            $query->has('projects');
        }

        if (!empty($post['keyword'])) {
            $keyword = $post['keyword'];
            $query->where(function($q) use ($keyword) {
                $q->where('company_name', 'like', "%{$keyword}%")
                  ->orWhere('license_number', 'like', "%{$keyword}%");
            });
        }

        if (!empty($post['rank'])) {
            $query->where('rank', $post['rank']);
        }

        $query->orderBy('ongoing_count', 'desc')
              ->orderBy('received_count', 'desc')
              ->orderBy('projects_count', 'desc')
              ->orderBy('id', 'desc');

        $perPage = $post['show_list_cnt'] ?? 20;
        $bizs = $query->paginate($perPage)->withQueryString();

        // フロントエンドの v-for="p in biz.ongoing_projects" で 
        // {{ p.name }} ではなく {{ p.title }} を使うか、ここで詰め替えます
        $bizs->getCollection()->transform(function ($biz) {
            $biz->ongoing_projects = $biz->projects->map(function($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->title // フロントが p.name を参照しているので title を name として渡す
                ];
            });
            return $biz;
        });

        $extraData = [
            'filters' => [
                'keyword' => $post['keyword'] ?? '',
                'rank'    => $post['rank'] ?? '',
                'status'  => $status,
            ]
        ];

        return [$bizs, $extraData];
    }
}