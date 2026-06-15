<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateTaskDueDateController extends Controller
{
    public function __invoke(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('view', $task);

        $validated = $request->validate([
            'due_date' => ['nullable', 'date'],
        ]);

        $task->update(['due_date' => $validated['due_date'] ?: null]);

        return back();
    }
}
