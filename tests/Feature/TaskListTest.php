<?php

use App\Livewire\TaskList;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('dashboard task list shows assigned tasks grouped by project', function () {
    $user = User::factory()->create();
    $status = Status::factory()->create(['is_closed' => false]);
    $project = Project::factory()->personallyOwnedBy($user)->create(['title' => 'Launch website']);

    $task = Task::query()->create([
        'title' => 'Write copy',
        'created_by_user_id' => $user->id,
    ]);
    $task->setOwner($project);
    $task->syncAssignees([$user]);
    $task->setStatus($status, $user);

    Livewire::actingAs($user)
        ->test(TaskList::class, ['context' => 'dashboard'])
        ->assertSee('Launch website')
        ->assertSee('Write copy');
});

test('project task list creates project owned tasks inline', function () {
    $user = User::factory()->create();
    Status::factory()->create(['is_closed' => false]);
    $project = Project::factory()->personallyOwnedBy($user)->create();

    Livewire::actingAs($user)
        ->test(TaskList::class, ['context' => 'project', 'project' => $project])
        ->set('title', 'Draft launch notes')
        ->set('dueDate', '2026-07-01')
        ->call('addTask')
        ->assertSet('title', '')
        ->assertSet('dueDate', null);

    $task = Task::query()->where('title', 'Draft launch notes')->firstOrFail();

    expect($task->owner_type)->toBe($project->getMorphClass())
        ->and($task->owner_id)->toBe($project->id)
        ->and($task->assignees()->whereKey($user->id)->exists())->toBeTrue()
        ->and($task->due_date?->format('Y-m-d'))->toBe('2026-07-01');
});

test('task status can be updated inline by an assignee', function () {
    $user = User::factory()->create();
    $pending = Status::factory()->create(['name' => 'Pending', 'is_closed' => false, 'sort_order' => 1]);
    $active = Status::factory()->create(['name' => 'Active', 'is_closed' => false, 'sort_order' => 2]);
    $task = Task::query()->create(['title' => 'Follow up', 'created_by_user_id' => $user->id]);

    $task->setOwner($user);
    $task->syncAssignees([$user]);
    $task->setStatus($pending, $user);

    Livewire::actingAs($user)
        ->test(TaskList::class, ['context' => 'dashboard'])
        ->call('updateStatus', $task->id, $active->id);

    expect($task->fresh()->status?->id)->toBe($active->id);
});

test('task due date can be updated inline', function () {
    $user = User::factory()->create();
    Status::factory()->create(['is_closed' => false]);
    $task = Task::query()->create(['title' => 'Schedule review', 'created_by_user_id' => $user->id]);

    $task->setOwner($user);
    $task->syncAssignees([$user]);

    Livewire::actingAs($user)
        ->test(TaskList::class, ['context' => 'dashboard'])
        ->call('updateDueDate', $task->id, '2026-07-15');

    expect($task->fresh()->due_date?->format('Y-m-d'))->toBe('2026-07-15');
});
