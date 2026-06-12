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

test('authenticated users can view public tasks across the organization', function () {
    $user = User::factory()->create();
    $assignee = User::factory()->create(['name' => 'Alex Rivera']);
    $team = Team::factory()->public()->create(['name' => 'Platform Team']);
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'completed']);

    $openTask = Task::query()->create([
        'title' => 'Open org task',
        'visibility' => 'public',
    ]);
    $openTask->setOwner($team);
    $openTask->syncAssignees([$assignee]);
    $openTask->syncTeams([$team]);
    $openTask->setStatus($openStatus);

    $closedTask = Task::query()->create([
        'title' => 'Closed org task',
        'visibility' => 'public',
    ]);
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

test('team members can see private team tasks they are not assigned to', function () {
    $viewer = User::factory()->create();
    $assignee = User::factory()->create(['name' => 'Jamie Lee']);
    $team = Team::factory()->create(['name' => 'Design Team']);
    $team->members()->attach($viewer);

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

test('users cannot see private team tasks for teams they do not belong to', function () {
    $viewer = User::factory()->create();
    $assignee = User::factory()->create(['name' => 'Jamie Lee']);
    $team = Team::factory()->create(['name' => 'Design Team']);

    $task = Task::query()->create(['title' => 'Hidden team task']);
    $task->setOwner($team);
    $task->syncAssignees([$assignee]);
    $task->syncTeams([$team]);
    $task->setStatus(Status::factory()->create(['slug' => 'pending', 'is_closed' => false]));

    $this->actingAs($viewer)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertDontSee('Hidden team task')
        ->assertDontSee('Jamie Lee');
});
