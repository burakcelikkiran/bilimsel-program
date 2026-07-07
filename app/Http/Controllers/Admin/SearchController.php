<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminSearchService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response
    {
        $results = $request->filled('q')
            ? app(AdminSearchService::class)->search(
                $request->user(),
                $request->string('q')->toString()
            )
            : [];

        $component = $request->header('X-Inertia-Partial-Component', 'Admin/Dashboard');

        return Inertia::render($component, [
            'searchResults' => $results,
        ]);
    }
}
