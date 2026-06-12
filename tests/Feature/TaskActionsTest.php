<?php

use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('assignees see status change options in the task actions menu', function () {
    $assignee = User::factory()->create();
    $status = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);

    $task = Task::query()->create(['title' => 'Assignable task']);
    $task->setOwner($assignee);
    $task->syncAssignees([$assignee]);
    $task->setStatus($status);

    $this->actingAs($assignee)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee(__('Set status'))
        ->assertSee($status->name);
});

test('non assignees do not see status change options for visible team tasks', function () {
    $assignee = User::factory()->create();
    $viewer = User::factory()->create();
    $team = Team::factory()->create();
    $status = Status::factory()->create(['slug' => 'pending', 'is_closed' => false, 'name' => 'Pending review']);

    $task = Task::query()->create(['title' => 'Shared team task']);
    $task->setOwner($team);
    $task->syncAssignees([$assignee]);
    $task->syncTeams([$team]);
    $task->setStatus($status);

    $response = $this->actingAs($viewer)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertSee('Shared team task');

    expect($response->getContent())
        ->not->toContain(__('Set status'))
        ->not->toContain('name="status_id"');
});
