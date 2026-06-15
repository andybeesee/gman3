<?php

namespace App\Livewire;

use App\Enums\Visibility;
use App\Models\Project;
use App\Models\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ProjectTaskTable extends Component
{
    use AuthorizesRequests;

    public Project $project;

    public string $title = '';

    public ?string $dueDate = null;

    public function addTask(): void
    {
        $this->authorize('view', $this->project);

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'dueDate' => ['nullable', 'date'],
        ]);

        $this->project->ownedTasks()->create([
            'title' => $this->title,
            'due_date' => $this->dueDate,
            'created_by_user_id' => auth()->id(),
            'visibility' => Visibility::Private,
        ]);

        $this->title = '';
        $this->dueDate = null;

        $this->dispatch('project-task-added');
    }

    public function render(): View
    {
        $tasks = $this->project->ownedTasks()
            ->with([
                'currentStatusChange.status',
                'assignees',
                'teams',
                'visibilityGrants.grantee',
            ])
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('id')
            ->get();

        return view('livewire.project-task-table', [
            'tasks' => $tasks,
            'statuses' => Status::query()->orderBy('sort_order')->get(),
        ]);
    }
}
