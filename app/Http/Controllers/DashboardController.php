<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Queries\RecordableQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the authenticated user's dashboard.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $projects = RecordableQuery::projectsVisibleTo($user)
            ->whereHas('ownedTasks', function ($query) use ($user) {
                $query->whereStatusOpen()
                    ->whereHas('assignees', fn ($q) => $q->whereKey($user->id));
            })
            ->with([
                'currentStatusChange.status',
                'ownedTasks' => function ($query) use ($user) {
                    $query->whereStatusOpen()
                        ->whereHas('assignees', fn ($q) => $q->whereKey($user->id))
                        ->with(['currentStatusChange.status', 'assignees'])
                        ->orderByRaw('due_date IS NULL')
                        ->orderBy('due_date');
                },
            ])
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->get();

        $standaloneTasks = $user->assignedTasks()
            ->whereStatusOpen()
            ->where(function ($query) {
                $query->where('owner_type', '!=', 'project')
                    ->orWhereNull('owner_type');
            })
            ->with(['currentStatusChange.status', 'teams', 'owner', 'visibilityGrants.grantee'])
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->get();

        return view('dashboard', [
            'projects' => $projects,
            'standaloneTasks' => $standaloneTasks,
            'statuses' => Status::query()->orderBy('sort_order')->get(),
        ]);
    }
}
