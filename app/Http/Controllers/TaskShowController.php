<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TaskShowController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Task $task): View
    {
        $this->authorize('view', $task);

        $task->load([
            'currentStatusChange.status',
            'assignees',
            'owner',
            'teams',
            'checklist',
            'visibilityGrants.grantee',
            'completedBy',
        ]);

        return view('tasks.show', [
            'task' => $task,
            'statuses' => Status::query()->orderBy('sort_order')->get(),
        ]);
    }
}
