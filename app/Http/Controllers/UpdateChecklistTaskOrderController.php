<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpdateChecklistTaskOrderController extends Controller
{
    use AuthorizesRequests;

    /**
     * Update the task order for the given checklist.
     */
    public function __invoke(Request $request, Checklist $checklist): JsonResponse|RedirectResponse
    {
        $this->authorize('reorderTasks', $checklist);

        $validated = $request->validate([
            'task_ids' => ['required', 'array'],
            'task_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $submittedTaskIds = array_map('intval', $validated['task_ids']);
        $currentTaskIds = $checklist->tasks()
            ->withoutGlobalScopes()
            ->pluck('tasks.id')
            ->map(fn (int $taskId): int => $taskId)
            ->all();

        $sortedSubmittedTaskIds = $submittedTaskIds;
        $sortedCurrentTaskIds = $currentTaskIds;

        sort($sortedSubmittedTaskIds);
        sort($sortedCurrentTaskIds);

        if ($sortedSubmittedTaskIds !== $sortedCurrentTaskIds) {
            $message = __('The task order must include every task in this checklist.');

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'errors' => [
                        'task_ids' => [$message],
                    ],
                ], 422);
            }

            return back()->withErrors([
                'task_ids' => $message,
            ]);
        }

        DB::transaction(function () use ($checklist, $submittedTaskIds): void {
            $checklist->tasks()
                ->withoutGlobalScopes()
                ->update(['checklist_position' => null]);

            foreach (array_values($submittedTaskIds) as $index => $taskId) {
                DB::table('tasks')
                    ->where('checklist_id', $checklist->id)
                    ->where('id', $taskId)
                    ->update(['checklist_position' => $index + 1]);
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('Task order updated.'),
            ]);
        }

        return redirect()
            ->route('checklists.show', $checklist)
            ->with('status', __('Task order updated.'));
    }
}
