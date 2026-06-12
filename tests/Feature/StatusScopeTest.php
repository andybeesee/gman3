<?php

use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tasks can be filtered by open status', function () {
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'completed']);

    $openTask = Task::query()->create(['title' => 'Open task']);
    $openTask->setStatus($openStatus);

    $closedTask = Task::query()->create(['title' => 'Closed task']);
    $closedTask->setStatus($closedStatus);

    expect(Task::query()->whereStatusOpen()->pluck('id'))
        ->toContain($openTask->id)
        ->not->toContain($closedTask->id);
});

test('tasks can be filtered by closed status', function () {
    $openStatus = Status::factory()->create(['slug' => 'in-progress', 'is_closed' => false]);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'cancelled']);

    $openTask = Task::query()->create(['title' => 'Open task']);
    $openTask->setStatus($openStatus);

    $closedTask = Task::query()->create(['title' => 'Closed task']);
    $closedTask->setStatus($closedStatus);

    expect(Task::query()->whereStatusClosed()->pluck('id'))
        ->toContain($closedTask->id)
        ->not->toContain($openTask->id);
});

test('dashboard excludes tasks with closed statuses', function () {
    $user = User::factory()->create();
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'completed']);

    $openTask = Task::query()->create(['title' => 'Open task']);
    $openTask->syncAssignees([$user]);
    $openTask->setStatus($openStatus);

    $closedTask = Task::query()->create(['title' => 'Closed task']);
    $closedTask->syncAssignees([$user]);
    $closedTask->setStatus($closedStatus);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Open task')
        ->assertDontSee('Closed task');
});
