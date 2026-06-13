<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ChecklistShowController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the given checklist and its ordered tasks.
     */
    public function __invoke(Checklist $checklist): View
    {
        $this->authorize('view', $checklist);

        $checklist->load(['owner', 'teams', 'visibilityGrants.grantee']);

        $tasks = $checklist->tasks()
            ->with(['currentStatusChange.status', 'assignees', 'teams', 'visibilityGrants.grantee'])
            ->get();

        return view('checklists.show', [
            'checklist' => $checklist,
            'tasks' => $tasks,
            'statuses' => Status::query()->orderBy('sort_order')->get(),
        ]);
    }
}
