<?php

use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('task morph relations store the task alias', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $status = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);

    $task = Task::query()->create(['title' => 'Mapped task']);
    $task->syncAssignees([$user]);
    $task->syncTeams([$team]);
    $task->setStatus($status, $user);

    expect(DB::table('assignables')->where('assignable_id', $task->id)->value('assignable_type'))
        ->toBe('task')
        ->and(DB::table('teamables')->where('teamable_id', $task->id)->value('teamable_type'))
        ->toBe('task')
        ->and(DB::table('status_changes')->where('statusable_id', $task->id)->value('statusable_type'))
        ->toBe('task');
});

test('morph map resolves task aliases back to task models', function () {
    $task = Task::query()->create(['title' => 'Resolved task']);
    $task->syncTeams([Team::factory()->create()]);

    $resolvedTask = Task::query()->find(
        DB::table('teamables')->where('teamable_type', 'task')->value('teamable_id'),
    );

    expect($resolvedTask?->is($task))->toBeTrue();
});

test('task owner morph stores the owner alias', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $userTask = Task::query()->create(['title' => 'User owned task']);
    $userTask->setOwner($user);

    $teamTask = Task::query()->create(['title' => 'Team owned task']);
    $teamTask->setOwner($team);

    expect(DB::table('tasks')->where('id', $userTask->id)->value('owner_type'))
        ->toBe('user')
        ->and(DB::table('tasks')->where('id', $teamTask->id)->value('owner_type'))
        ->toBe('team')
        ->and($userTask->fresh()->owner?->is($user))->toBeTrue()
        ->and($teamTask->fresh()->owner?->is($team))->toBeTrue();
});
