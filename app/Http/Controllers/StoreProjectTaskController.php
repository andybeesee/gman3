<?php

namespace App\Http\Controllers;

use App\Enums\Visibility;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreProjectTaskController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('view', $project);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
        ]);

        $project->ownedTasks()->create([
            'title' => $validated['title'],
            'due_date' => $validated['due_date'] ?? null,
            'created_by_user_id' => $request->user()->id,
            'visibility' => Visibility::Private,
        ]);

        return back()->with('status', __('Task added to ":project".', ['project' => $project->title]));
    }
}
