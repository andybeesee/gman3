<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TaskIndexController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display all tasks across the organization.
     */
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Task::query()
            ->with(['currentStatusChange.status', 'assignees', 'teams'])
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('id')
            ->paginate(50);

        return view('tasks.index', [
            'tasks' => $tasks,
            'statuses' => Status::query()->orderBy('sort_order')->get(),
        ]);
    }
}
