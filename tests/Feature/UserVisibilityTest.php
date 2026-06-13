<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('users cannot see private tasks owned or assigned to other users unless related', function () {
    $roger = User::factory()->create(['name' => 'Roger']);
    $don = User::factory()->create(['name' => 'Don']);
    $peggy = User::factory()->create(['name' => 'Peggy']);

    $donTask = Task::query()->create(['title' => 'Don private task']);
    $donTask->setOwner($don);

    $peggyTask = Task::query()->create(['title' => 'Peggy private task']);
    $peggyTask->setOwner($peggy);

    $strangerTask = Task::query()->create(['title' => 'Stranger private task']);
    $strangerTask->setOwner(User::factory()->create());

    expect(Task::query()->visibleTo($roger)->pluck('title')->all())->not->toContain('Don private task', 'Peggy private task')
        ->and(Task::query()->visibleTo($roger)->pluck('title')->all())->not->toContain('Stranger private task')
        ->and(Task::query()->visibleTo($don)->pluck('title')->all())->toContain('Don private task')
        ->and(Task::query()->visibleTo($don)->pluck('title')->all())->not->toContain('Peggy private task');
});

test('users cannot see private projects owned by other users unless shared', function () {
    $roger = User::factory()->create(['name' => 'Roger']);
    $don = User::factory()->create(['name' => 'Don']);

    $donProject = Project::factory()->privateForUser($don)->create(['title' => 'Don private project']);
    $strangerProject = Project::factory()->privateForUser(User::factory()->create())->create([
        'title' => 'Stranger private project',
    ]);

    expect(Project::query()->visibleTo($roger)->pluck('title')->all())->not->toContain('Don private project', 'Stranger private project');

    $donProject->grantAccessTo($roger);

    expect(Project::query()->visibleTo($roger)->pluck('title')->all())->toContain('Don private project')
        ->and(Project::query()->visibleTo($roger)->pluck('title')->all())->not->toContain('Stranger private project');
});

test('authenticated users can see all users', function () {
    $viewer = User::factory()->create();
    $other = User::factory()->create();

    expect($viewer->can('view', $other))->toBeTrue();
});
