<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\UseCases\Biz\IndexAction;
use App\UseCases\Biz\FormAction;

class BizController extends Controller
{
    public function index(Request $request, IndexAction $case)
    {
        $post = $request->all();

        // list($post, ) = $case($post);

        return Inertia::render('Biz/list', [
        ]);
    }

    public function edit(Request $request, FormAction $case)
    {

        // list($demand, $quote_headers, $data, $remarks_to_gm, $remarks_to_bp) = $case($demand_id, $customer_id);

        return Inertia::render('Biz/form', [
        ]);
    }
}
