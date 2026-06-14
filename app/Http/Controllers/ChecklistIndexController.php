<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Queries\RecordableQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ChecklistIndexController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display checklists visible to the authenticated user.
     */
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Checklist::class);

        $checklists = RecordableQuery::checklistsVisibleTo($request->user())
            ->with([
                'owner',
                'teams' => fn ($query) => $query->visibleTo($request->user()),
                'visibilityGrants.grantee',
            ])
            ->withCount('tasks')
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('id')
            ->paginate(50);

        return view('checklists.index', [
            'checklists' => $checklists,
        ]);
    }
}
