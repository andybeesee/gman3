<?php

use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('assignee can update task status from the dashboard', function () {
    $user = User::factory()->create();
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $nextStatus = Status::factory()->create(['slug' => 'in-progress', 'is_closed' => false]);

    $task = Task::query()->create(['title' => 'Ship feature']);
    $task->syncAssignees([$user]);
    $task->setStatus($openStatus);

    $this->actingAs($user)
        ->patch(route('tasks.status.update', $task), [
            'status_id' => $nextStatus->id,
        ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status');

    $task->refresh();

    expect($task->status?->id)->toBe($nextStatus->id)
        ->and($task->completed_at)->toBeNull()
        ->and($task->completed_by_user_id)->toBeNull();
});

test('assignee setting a closed status records completion details', function () {
    $user = User::factory()->create();
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'completed']);

    $task = Task::query()->create(['title' => 'Ship feature']);
    $task->syncAssignees([$user]);
    $task->setStatus($openStatus);

    $this->actingAs($user)
        ->patch(route('tasks.status.update', $task), [
            'status_id' => $closedStatus->id,
        ])
        ->assertRedirect(route('dashboard'));

    $task->refresh();

    expect($task->status?->id)->toBe($closedStatus->id)
        ->and($task->completed_at)->not->toBeNull()
        ->and($task->completed_by_user_id)->toBe($user->id);
});

test('assignee reopening a task clears completion details', function () {
    $user = User::factory()->create();
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'cancelled']);

    $task = Task::query()->create(['title' => 'Ship feature']);
    $task->syncAssignees([$user]);
    $task->setStatus($closedStatus, $user);

    $this->actingAs($user)
        ->patch(route('tasks.status.update', $task), [
            'status_id' => $openStatus->id,
        ])
        ->assertRedirect(route('dashboard'));

    $task->refresh();

    expect($task->status?->id)->toBe($openStatus->id)
        ->and($task->completed_at)->toBeNull()
        ->and($task->completed_by_user_id)->toBeNull();
});

test('users who are not assignees cannot update task status', function () {
    $assignee = User::factory()->create();
    $otherUser = User::factory()->create();
    $team = Team::factory()->create();
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $nextStatus = Status::factory()->create(['slug' => 'in-progress', 'is_closed' => false]);

    $task = Task::query()->create(['title' => 'Ship feature']);
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
