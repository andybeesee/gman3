<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateTaskStatusController extends Controller
{
    use AuthorizesRequests;

    /**
     * Update the status of the given task.
     */
    public function __invoke(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('updateStatus', $task);

        $validated = $request->validate([
            'status_id' => ['required', 'integer', 'exists:statuses,id'],
        ]);

        $status = Status::query()->findOrFail($validated['status_id']);

        $task->setStatus($status, $request->user());

        return to_route('dashboard')->with('status', __('Task status updated.'));
    }
}
