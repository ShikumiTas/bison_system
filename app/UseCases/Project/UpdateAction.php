<?php

namespace App\UseCases\Project;

use App\Models\Project;

class UpdateAction
{
    public function __invoke($id, array $data)
    {
        $project = Project::findOrFail($id);

        // 更新対象を金額とメモに限定してセーフティに更新
        $project->update([
            'status' => $data['status'] ?? 0,
            'expected_amount' => $data['expected_amount'] ?? null,
            'status_memo'     => $data['status_memo'] ?? null,
        ]);

        return $project;
    }
}