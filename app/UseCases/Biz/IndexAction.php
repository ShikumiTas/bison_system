<?php

namespace App\UseCases\Biz;

use Illuminate\Support\Facades\DB;

class IndexAction
{

    public function __invoke($post)
    {
        return [$post];
    }
}
