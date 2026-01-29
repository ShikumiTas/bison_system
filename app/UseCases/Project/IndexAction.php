<?php

namespace App\UseCases\Project;

use Illuminate\Support\Facades\DB;

class IndexAction
{
    public function __invoke($post)
    {

        return [$post,];
    }

}
