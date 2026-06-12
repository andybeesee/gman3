<?php

use App\Models\Scopes\VisibleToAuthenticatedUserScope;
use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('personal tasks are hidden from users who are not assignees', function () {
    $viewer = User::factory()->create();
    $assignee = User::factory()->create(['name' => 'Private Owner']);

    $task = Task::query()->create(['title' => 'Private personal task']);
    $task->syncAssignees([$assignee]);
    $task->setStatus(Status::factory()->create(['slug' => 'pending', 'is_closed' => false]));

    expect($task->isPersonal())->toBeTrue();

    $this->actingAs($viewer)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertDontSee('Private personal task')
        ->assertDontSee('Private Owner');
});

test('assignees can see their personal tasks on the task index', function () {
    $assignee = User::factory()->create(['name' => 'Private Owner']);

    $task = Task::query()->create(['title' => 'Private personal task']);
    $task->syncAssignees([$assignee]);
    $task->setStatus(Status::factory()->create(['slug' => 'pending', 'is_closed' => false]));

    $this->actingAs($assignee)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertSee('Private personal task')
        ->assertSee('Private Owner');
});

test('tasks with teams are not considered personal', function () {
    $task = Task::query()->create(['title' => 'Team task']);
    $task->syncTeams([Team::factory()->create()]);

    expect($task->isPersonal())->toBeFalse();
});

test('visible to scope hides personal tasks from non assignees', function () {
    $viewer = User::factory()->create();
    $assignee = User::factory()->create();

    $personalTask = Task::query()->create(['title' => 'Personal task']);
    $personalTask->syncAssignees([$assignee]);

    $teamTask = Task::query()->create(['title' => 'Team task']);
    $teamTask->syncTeams([Team::factory()->create()]);

    $visibleIds = Task::query()->visibleTo($viewer)->pluck('id');

    expect($visibleIds)
        ->toContain($teamTask->id)
        ->not->toContain($personalTask->id);
});

test('users cannot update personal tasks they cannot see', function () {
    $assignee = User::factory()->create();
    $otherUser = User::factory()->create();
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $nextStatus = Status::factory()->create(['slug' => 'in-progress', 'is_closed' => false]);

    $task = Task::query()->create(['title' => 'Private task']);
    $task->syncAssignees([$assignee]);
    $task->setStatus($openStatus);

    $this->actingAs($otherUser)
        ->patch(route('tasks.status.update', $task), [
            'status_id' => $nextStatus->id,
        ])
        ->assertNotFound();

    expect(Task::withoutGlobalScope(VisibleToAuthenticatedUserScope::class)->find($task->id)?->status?->id)
        ->toBe($openStatus->id);
});

test('non assignees cannot update visible team tasks', function () {
    $assignee = User::factory()->create();
    $otherUser = User::factory()->create();
    $team = Team::factory()->create();
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $nextStatus = Status::factory()->create(['slug' => 'in-progress', 'is_closed' => false]);

    $task = Task::query()->create(['title' => 'Shared team task']);
    $task->syncAssignees([$assignee]);
    $task->syncTeams([$team]);
    $task->setStatus($openStatus);

    $this->actingAs($otherUser)
        ->patch(route('tasks.status.update', $task), [
            'status_id' => $nextStatus->id,
        ])
        ->assertForbidden();

    expect($task->fresh()->status?->id)->toBe($openStatus->id);
});
