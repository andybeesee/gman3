<?php

use App\Models\Scopes\VisibleToAuthenticatedUserScope;
use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('private user owned tasks are hidden from users who are not assignees', function () {
    $viewer = User::factory()->create();
    $assignee = User::factory()->create(['name' => 'Private Owner']);

    $task = Task::query()->create(['title' => 'Private personal task']);
    $task->setOwner($assignee);
    $task->syncAssignees([$assignee]);
    $task->setStatus(Status::factory()->create(['slug' => 'pending', 'is_closed' => false]));

    expect($task->isUserOwned())->toBeTrue();

    $this->actingAs($viewer)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertDontSee('Private personal task')
        ->assertDontSee('Private Owner');
});

test('assignees can see their private user owned tasks on the task index', function () {
    $assignee = User::factory()->create(['name' => 'Private Owner']);

    $task = Task::query()->create(['title' => 'Private personal task']);
    $task->setOwner($assignee);
    $task->syncAssignees([$assignee]);
    $task->setStatus(Status::factory()->create(['slug' => 'pending', 'is_closed' => false]));

    $this->actingAs($assignee)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertSee('Private personal task')
        ->assertSee('Private Owner');
});

test('tasks owned by teams are not considered user owned', function () {
    $team = Team::factory()->create();
    $task = Task::query()->create(['title' => 'Team task']);
    $task->setOwner($team);

    expect($task->isUserOwned())->toBeFalse();
});

test('private team owned tasks are only visible to team members and assignees', function () {
    $viewer = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($member);

    $teamTask = Task::query()->create(['title' => 'Team task']);
    $teamTask->setOwner($team);
    $teamTask->syncTeams([$team]);

    $visibleToViewer = Task::query()->visibleTo($viewer)->pluck('id');
    $visibleToMember = Task::query()->visibleTo($member)->pluck('id');

    expect($visibleToViewer)->not->toContain($teamTask->id)
        ->and($visibleToMember)->toContain($teamTask->id);
});

test('public tasks are visible to every authenticated user', function () {
    $viewer = User::factory()->create();
    $team = Team::factory()->create();

    $task = Task::query()->create([
        'title' => 'Public team task',
        'visibility' => 'public',
    ]);
    $task->setOwner($team);

    expect(Task::query()->visibleTo($viewer)->pluck('id'))->toContain($task->id);
});

test('users cannot update private tasks they cannot see', function () {
    $assignee = User::factory()->create();
    $otherUser = User::factory()->create();
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $nextStatus = Status::factory()->create(['slug' => 'in-progress', 'is_closed' => false]);

    $task = Task::query()->create(['title' => 'Private task']);
    $task->setOwner($assignee);
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
    $member = User::factory()->create();
    $otherUser = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($member);
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $nextStatus = Status::factory()->create(['slug' => 'in-progress', 'is_closed' => false]);

    $task = Task::query()->create(['title' => 'Shared team task']);
    $task->setOwner($team);
    $task->syncAssignees([$assignee]);
    $task->syncTeams([$team]);
    $task->setStatus($openStatus);

    $this->actingAs($otherUser)
        ->patch(route('tasks.status.update', $task), [
            'status_id' => $nextStatus->id,
        ])
        ->assertNotFound();

    $this->actingAs($member)
        ->patch(route('tasks.status.update', $task), [
            'status_id' => $nextStatus->id,
        ])
        ->assertForbidden();

    expect($task->fresh()->status?->id)->toBe($openStatus->id);
});
