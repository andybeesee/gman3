<?php

namespace App\Livewire;

use App\Enums\Visibility;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

class TaskList extends Component
{
    use AuthorizesRequests;

    public string $context = 'dashboard';

    public ?Project $project = null;

    public string $title = '';

    public ?string $dueDate = null;

    public function addTask(): void
    {
        $this->authorize('create', Task::class);

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'dueDate' => ['nullable', 'date'],
        ]);

        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $task = Task::query()->create([
            'title' => $this->title,
            'due_date' => $this->dueDate,
            'created_by_user_id' => $user->id,
            'visibility' => Visibility::Private,
        ]);

        if ($this->context === 'project' && $this->project !== null) {
            $this->authorize('view', $this->project);

            $task->setOwner($this->project);
            $task->syncTeams($this->project->teams);
        } else {
            $task->setOwner($user);
        }

        $task->syncAssignees([$user]);

        $this->title = '';
        $this->dueDate = null;

        $this->dispatch('task-list-updated');
    }

    public function updateStatus(int $taskId, int $statusId): void
    {
        $task = Task::query()->findOrFail($taskId);
        $status = Status::query()->findOrFail($statusId);

        $this->authorize('updateStatus', $task);

        $task->setStatus($status, auth()->user());

        $this->dispatch('task-list-updated');
    }

    public function updateDueDate(int $taskId, ?string $dueDate): void
    {
        $task = Task::query()->findOrFail($taskId);

        $this->authorize('view', $task);

        validator(['dueDate' => $dueDate], [
            'dueDate' => ['nullable', 'date'],
        ])->validate();

        $task->update(['due_date' => $dueDate ?: null]);

        $this->dispatch('task-list-updated');
    }

    public function render(): View
    {
        $tasks = $this->tasks();

        return view('livewire.task-list', [
            'tasks' => $tasks,
            'taskGroups' => $this->taskGroups($tasks),
            'statuses' => Status::query()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * @return Collection<int, Task>
     */
    protected function tasks(): Collection
    {
        $query = Task::query()
            ->with([
                'currentStatusChange.status',
                'assignees',
                'teams',
                'owner',
                'visibilityGrants.grantee',
            ])
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('id');

        if ($this->context === 'project' && $this->project !== null) {
            return $query
                ->where('owner_type', $this->project->getMorphClass())
                ->where('owner_id', $this->project->id)
                ->get();
        }

        $user = auth()->user();

        if (! $user instanceof User) {
            return collect();
        }

        return $query
            ->whereStatusOpen()
            ->whereHas('assignees', fn (Builder $query) => $query->whereKey($user->id))
            ->get();
    }

    /**
     * @param  Collection<int, Task>  $tasks
     * @return Collection<string, Collection<int, Task>>
     */
    protected function taskGroups(Collection $tasks): Collection
    {
        if ($this->context !== 'dashboard') {
            return collect(['tasks' => $tasks]);
        }

        return $tasks->groupBy(fn (Task $task): string => $this->groupKey($task));
    }

    protected function groupKey(Task $task): string
    {
        if ($task->owner instanceof Project) {
            return 'project:'.$task->owner->id;
        }

        return $task->owner_type.':'.$task->owner_id;
    }
}
