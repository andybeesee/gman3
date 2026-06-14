<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TaskCreateController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show the task creation form.
     */
    public function __invoke(Request $request): View
    {
        $this->authorize('create', Task::class);

        return view('tasks.create', [
            'assignees' => User::query()->orderBy('name')->get(),
            'projects' => Project::query()->visibleTo($request->user())->orderBy('title')->get(),
            'teams' => Team::query()->visibleTo($request->user())->orderBy('name')->get(),
        ]);
    }
}
