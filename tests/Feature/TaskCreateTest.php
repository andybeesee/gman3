<?php

use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot create tasks', function () {
    $this->get(route('tasks.create'))
        ->assertRedirect(route('login'));

    $this->post(route('tasks.store'), [])
        ->assertRedirect(route('login'));
});

test('authenticated users can view the new task form', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Delivery Team']);
    $team->addMember($user);
    $project = Project::factory()->privateForUser($user)->create(['title' => 'Client Portal']);

    $this->actingAs($user)
        ->get(route('tasks.create'))
        ->assertSuccessful()
        ->assertSee('New task')
        ->assertSee('Delivery Team')
        ->assertSee('Client Portal')
        ->assertSee('data-multi-select', false)
        ->assertSee('Choose assignees')
        ->assertSee('Choose teams');
});

test('authenticated users can create a personal task', function () {
    $user = User::factory()->create();
    Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);

    $response = $this->actingAs($user)
        ->post(route('tasks.store'), [
            'title' => 'Draft onboarding checklist',
            'description' => 'Write the first pass.',
            'start_date' => '2026-06-14',
            'due_date' => '2026-06-20',
            'visibility' => 'private',
            'owner_type' => 'user',
            'assignee_ids' => [$user->id],
        ]);

    $task = Task::query()->where('title', 'Draft onboarding checklist')->firstOrFail();

    $response
        ->assertRedirect(route('tasks.show', $task))
        ->assertSessionHas('status', __('Task created.'));

    expect($task->created_by_user_id)->toBe($user->id)
        ->and($task->owner_type)->toBe('user')
        ->and($task->owner_id)->toBe($user->id)
        ->and($task->assignees()->whereKey($user->id)->exists())->toBeTrue()
        ->and($task->record?->title)->toBe('Draft onboarding checklist');
});

test('authenticated users can create a project owned task', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->addMember($user);
    $project = Project::factory()->privateForTeam($user)->create(['title' => 'Internal rollout']);
    $project->syncTeams([$team]);

    $response = $this->actingAs($user)
        ->post(route('tasks.store'), [
            'title' => 'Schedule rollout review',
            'visibility' => 'private',
            'owner_type' => 'project',
            'owner_project_id' => $project->id,
            'assignee_ids' => [$user->id],
        ]);

    $task = Task::query()->where('title', 'Schedule rollout review')->firstOrFail();

    $response->assertRedirect(route('tasks.show', $task));

    expect($task->owner_type)->toBe('project')
        ->and($task->owner_id)->toBe($project->id)
        ->and($task->teams()->whereKey($team->id)->exists())->toBeTrue();
});

test('users cannot create tasks for hidden teams', function () {
    $user = User::factory()->create();
    $hiddenTeam = Team::factory()->create();

    $this->actingAs($user)
        ->from(route('tasks.create'))
        ->post(route('tasks.store'), [
            'title' => 'Hidden team task',
            'visibility' => 'private',
            'owner_type' => 'team',
            'owner_team_id' => $hiddenTeam->id,
        ])
        ->assertRedirect(route('tasks.create'))
        ->assertSessionHasErrors('owner_team_id');

    expect(Task::query()->where('title', 'Hidden team task')->exists())->toBeFalse();
});
