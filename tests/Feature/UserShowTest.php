<?php

use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot view a user profile', function () {
    $user = User::factory()->create();

    $this->get(route('users.show', $user))
        ->assertRedirect(route('login'));
});

test('dashboard tab shows active visible projects and tasks', function () {
    $user = User::factory()->create([
        'name' => 'Don',
        'email' => 'don@example.com',
    ]);
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false, 'name' => 'Planning']);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'completed']);

    $project = Project::factory()->create(['title' => 'Don rollout', 'owner_user_id' => $user->id]);
    $project->setStatus($openStatus);

    $closedProject = Project::factory()->create(['title' => 'Archived rollout', 'owner_user_id' => $user->id]);
    $closedProject->setStatus($closedStatus);

    $openTask = Task::query()->create(['title' => 'Prepare launch checklist']);
    $openTask->setOwner($user);
    $openTask->syncAssignees([$user]);
    $openTask->setStatus($openStatus);

    $closedTask = Task::query()->create(['title' => 'Archived checklist item']);
    $closedTask->setOwner($user);
    $closedTask->syncAssignees([$user]);
    $closedTask->setStatus($closedStatus);

    $this->actingAs($user)
        ->get(route('users.show', $user))
        ->assertSuccessful()
        ->assertSee(__('Dashboard'), false)
        ->assertSee('Don')
        ->assertSee('don@example.com')
        ->assertSee('Don rollout')
        ->assertSee('Planning')
        ->assertSee('Prepare launch checklist')
        ->assertDontSee('Archived rollout')
        ->assertDontSee('Archived checklist item');
});

test('users can view another users projects tab without seeing private projects', function () {
    $viewer = User::factory()->create();
    $user = User::factory()->create(['name' => 'Don']);
    $privateProject = Project::factory()->privateForUser($user)->create(['title' => 'Private rollout']);
    $privateProject->setStatus(Status::factory()->create(['slug' => 'pending', 'is_closed' => false]));

    $this->actingAs($viewer)
        ->get(route('users.show', ['user' => $user, 'tab' => 'projects']))
        ->assertSuccessful()
        ->assertSee(__('All projects'))
        ->assertDontSee('Private rollout');
});

test('projects tab lists visible projects for the user', function () {
    $user = User::factory()->create(['name' => 'Don']);
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);

    $openProject = Project::factory()->create(['title' => 'Open rollout', 'owner_user_id' => $user->id]);
    $openProject->setStatus($openStatus);

    $closedProject = Project::factory()->create(['title' => 'Closed rollout', 'owner_user_id' => $user->id]);
    $closedProject->setStatus(Status::factory()->closed()->create(['slug' => 'completed']));

    $this->actingAs($user)
        ->get(route('users.show', ['user' => $user, 'tab' => 'projects']))
        ->assertSuccessful()
        ->assertSee(__('All projects'))
        ->assertSee('Open rollout')
        ->assertSee('Closed rollout');
});

test('tasks tab lists all related tasks for the user', function () {
    $user = User::factory()->create(['name' => 'Don']);
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'completed']);

    $openTask = Task::query()->create(['title' => 'Open checklist item']);
    $openTask->setOwner($user);
    $openTask->syncAssignees([$user]);
    $openTask->setStatus($openStatus);

    $closedTask = Task::query()->create(['title' => 'Closed checklist item']);
    $closedTask->setOwner($user);
    $closedTask->syncAssignees([$user]);
    $closedTask->setStatus($closedStatus);

    $this->actingAs($user)
        ->get(route('users.show', ['user' => $user, 'tab' => 'tasks']))
        ->assertSuccessful()
        ->assertSee(__('All tasks'))
        ->assertSee('Open checklist item')
        ->assertSee('Closed checklist item');
});

test('users can view coworker profiles', function () {
    $viewer = User::factory()->create();
    $other = User::factory()->create(['name' => 'Visible User']);

    $this->actingAs($viewer)
        ->get(route('users.show', $other))
        ->assertSuccessful()
        ->assertSee('Visible User');
});
