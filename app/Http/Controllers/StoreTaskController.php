<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class StoreTaskController extends Controller
{
    /**
     * Store a newly created task.
     */
    public function __invoke(StoreTaskRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated): void {
            $task = Task::query()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'visibility' => $validated['visibility'],
                'created_by_user_id' => $request->user()->id,
            ]);

            $owner = $this->ownerFrom($validated, $request->user());
            $task->setOwner($owner);

            $assigneeIds = collect($validated['assignee_ids'] ?? [$request->user()->id])
                ->filter()
                ->unique()
                ->values();

            $task->syncAssignees($assigneeIds);

            $teamIds = collect($validated['team_ids'] ?? []);

            if ($owner instanceof Team) {
                $teamIds->push($owner->id);
            }

            if ($owner instanceof Project) {
                $teamIds = $teamIds->merge($owner->teams()->pluck('teams.id'));
            }

            $task->syncTeams($teamIds->filter()->unique()->values());
        });

        return redirect()->route('tasks.index')->with('status', __('Task created.'));
    }

    /**
     * @param  array{owner_type: string, owner_team_id?: int|null, owner_project_id?: int|null}  $validated
     */
    protected function ownerFrom(array $validated, Model $defaultOwner): Model
    {
        return match ($validated['owner_type']) {
            'team' => Team::query()->findOrFail($validated['owner_team_id']),
            'project' => Project::query()->findOrFail($validated['owner_project_id']),
            default => $defaultOwner,
        };
    }
}
