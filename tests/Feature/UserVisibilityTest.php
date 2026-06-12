<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('supervisors can see private tasks owned or assigned to their reports', function () {
    $roger = User::factory()->create(['name' => 'Roger']);
    $don = User::factory()->forSupervisor($roger)->create(['name' => 'Don']);
    $peggy = User::factory()->forSupervisor($don)->create(['name' => 'Peggy']);

    $donTask = Task::query()->create(['title' => 'Don private task']);
    $donTask->setOwner($don);

    $peggyTask = Task::query()->create(['title' => 'Peggy private task']);
    $peggyTask->setOwner($peggy);

    $strangerTask = Task::query()->create(['title' => 'Stranger private task']);
    $strangerTask->setOwner(User::factory()->create());

    expect(Task::query()->visibleTo($roger)->pluck('title')->all())->toContain('Don private task', 'Peggy private task')
        ->and(Task::query()->visibleTo($roger)->pluck('title')->all())->not->toContain('Stranger private task')
        ->and(Task::query()->visibleTo($don)->pluck('title')->all())->toContain('Don private task', 'Peggy private task');
});

test('supervisors can see private projects owned by their reports', function () {
    $roger = User::factory()->create(['name' => 'Roger']);
    $don = User::factory()->forSupervisor($roger)->create(['name' => 'Don']);

    $donProject = Project::factory()->privateForUser($don)->create(['title' => 'Don private project']);
    $strangerProject = Project::factory()->privateForUser(User::factory()->create())->create([
        'title' => 'Stranger private project',
    ]);

    expect(Project::query()->visibleTo($roger)->pluck('title')->all())->toContain('Don private project')
        ->and(Project::query()->visibleTo($roger)->pluck('title')->all())->not->toContain('Stranger private project');
});

test('super admins can see all users', function () {
    $admin = User::factory()->superAdmin()->create();
    $other = User::factory()->create();

    expect(User::query()->visibleTo($admin)->pluck('id'))->toContain($other->id)
        ->and($admin->can('view', $other))->toBeTrue();
});
