<?php

namespace App\Http\Controllers;

use App\Enums\Visibility;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreTaskController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'visibility' => ['required', 'in:public,private'],
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'visibility' => Visibility::from($validated['visibility']),
            'created_by_user_id' => $request->user()->id,
        ]);

        $task->owner()->associate($request->user())->save();

        return redirect()->route('tasks.show', $task)
            ->with('status', __('Task created.'));
    }
}
