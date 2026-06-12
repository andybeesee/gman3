<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('super admins can see all private teams', function () {
    $admin = User::factory()->superAdmin()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Executive Team']);
    $team->members()->attach($member);

    expect(Team::query()->visibleTo($admin)->pluck('id'))->toContain($team->id)
        ->and(Team::query()->visibleTo($member)->pluck('id'))->toContain($team->id)
        ->and($admin->can('view', $team))->toBeTrue();
});

test('super admins can see all private projects', function () {
    $admin = User::factory()->superAdmin()->create();
    $owner = User::factory()->create();

    $project = Project::factory()->privateForUser($owner)->create([
        'title' => 'Executive initiative',
    ]);

    expect(Project::query()->visibleTo($admin)->pluck('id'))->toContain($project->id)
        ->and($admin->can('view', $project))->toBeTrue()
        ->and(User::factory()->create()->can('view', $project))->toBeFalse();
});

test('super admins still follow normal task visibility rules', function () {
    $admin = User::factory()->superAdmin()->create();
    $assignee = User::factory()->create();
    $team = Team::factory()->create();

    $task = Task::query()->create(['title' => 'Hidden team task']);
    $task->setOwner($team);
    $task->syncAssignees([$assignee]);

    expect(Task::query()->visibleTo($admin)->pluck('id'))->not->toContain($task->id)
        ->and($admin->can('view', $task))->toBeFalse();
});

test('admin example user is seeded as a super admin', function () {
    $this->seed(UserSeeder::class);

    $admin = User::query()->where('email', 'admin@example.com')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->isSuperAdmin())->toBeTrue();
});
