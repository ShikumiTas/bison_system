<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\UseCases\Project\IndexAction;
use App\UseCases\Project\FormAction;

class ProjectController extends Controller
{
    public function index(Request $request, IndexAction $case)
    {
        $post = $request->all();

        // list($post, ) = $case($post);

        return Inertia::render('Project/list', [
        ]);
    }

    public function edit(Request $request, FormAction $case)
    {

        // list($demand, $quote_headers, $data, $remarks_to_gm, $remarks_to_bp) = $case($demand_id, $customer_id);

        return Inertia::render('Project/form', [
        ]);
    }

}
