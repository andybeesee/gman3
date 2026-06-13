<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ProjectShowController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the given project and its tasks.
     */
    public function __invoke(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        $project->load(['currentStatusChange.status', 'teams', 'ownerUser', 'visibilityGrants.grantee']);

        $tasks = $project->ownedTasks()
            ->with(['currentStatusChange.status', 'assignees', 'teams', 'visibilityGrants.grantee'])
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('id')
            ->paginate(50);

        return view('projects.show', [
            'project' => $project,
            'tasks' => $tasks,
            'statuses' => Status::query()->orderBy('sort_order')->get(),
        ]);
    }
}
