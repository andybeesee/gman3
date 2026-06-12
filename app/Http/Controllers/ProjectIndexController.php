<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ProjectIndexController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display projects visible to the authenticated user.
     */
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::query()
            ->with([
                'currentStatusChange.status',
                'teams' => fn ($query) => $query->visibleTo($request->user()),
                'ownerUser',
            ])
            ->withCount('ownedTasks')
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('id')
            ->paginate(50);

        return view('projects.index', [
            'projects' => $projects,
        ]);
    }
}
