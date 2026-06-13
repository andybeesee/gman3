<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the authenticated user's dashboard.
     */
    public function __invoke(Request $request): View
    {
        $tasks = $request->user()
            ->assignedTasks()
            ->whereStatusOpen()
            ->with(['currentStatusChange.status', 'teams', 'owner', 'visibilityGrants.grantee'])
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('id')
            ->paginate(50);

        return view('dashboard', [
            'tasks' => $tasks,
            'statuses' => Status::query()->orderBy('sort_order')->get(),
        ]);
    }
}
