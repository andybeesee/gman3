<?php

use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot view the task index', function () {
    $this->get(route('tasks.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view team tasks across the organization', function () {
    $user = User::factory()->create();
    $assignee = User::factory()->create(['name' => 'Alex Rivera']);
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'completed']);

    $openTask = Task::query()->create(['title' => 'Open org task']);
    $openTask->setOwner($team);
    $openTask->syncAssignees([$assignee]);
    $openTask->syncTeams([$team]);
    $openTask->setStatus($openStatus);

    $closedTask = Task::query()->create(['title' => 'Closed org task']);
    $closedTask->setOwner($team);
    $closedTask->syncAssignees([$assignee]);
    $closedTask->syncTeams([$team]);
    $closedTask->setStatus($closedStatus);

    $this->actingAs($user)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertSee('Open org task')
        ->assertSee('Closed org task')
        ->assertSee('Alex Rivera')
        ->assertSee('Platform Team');
});

test('task index shows team tasks not assigned to the current user', function () {
    $viewer = User::factory()->create();
    $assignee = User::factory()->create(['name' => 'Jamie Lee']);
    $team = Team::factory()->create(['name' => 'Design Team']);

    $task = Task::query()->create(['title' => 'Someone else task']);
    $task->setOwner($team);
    $task->syncAssignees([$assignee]);
    $task->syncTeams([$team]);
    $task->setStatus(Status::factory()->create(['slug' => 'pending', 'is_closed' => false]));

    $this->actingAs($viewer)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertSee('Someone else task')
        ->assertSee('Jamie Lee')
        ->assertSee('Design Team');
});
