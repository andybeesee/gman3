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

test('dashboard tab shows active projects tasks and direct reports', function () {
    $supervisor = User::factory()->create(['name' => 'Roger']);
    $user = User::factory()->forSupervisor($supervisor)->create([
        'name' => 'Don',
        'email' => 'don@example.com',
    ]);
    $report = User::factory()->forSupervisor($user)->create(['name' => 'Peggy']);
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

    $this->actingAs($supervisor)
        ->get(route('users.show', $user))
        ->assertSuccessful()
        ->assertSee(__('Dashboard'), false)
        ->assertSee('Don')
        ->assertSee('don@example.com')
        ->assertSee('Peggy')
        ->assertSee('Don rollout')
        ->assertSee('Planning')
        ->assertSee('Prepare launch checklist')
        ->assertDontSee('Archived rollout')
        ->assertDontSee('Archived checklist item');
});

test('users cannot view another users projects tab when not in their reporting line', function () {
    $viewer = User::factory()->create();
    $user = User::factory()->create(['name' => 'Don']);

    $this->actingAs($viewer)
        ->get(route('users.show', ['user' => $user, 'tab' => 'projects']))
        ->assertNotFound();
});

test('supervisor can view projects tab for a report', function () {
    $supervisor = User::factory()->create();
    $user = User::factory()->forSupervisor($supervisor)->create(['name' => 'Don']);
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);

    $openProject = Project::factory()->create(['title' => 'Open rollout', 'owner_user_id' => $user->id]);
    $openProject->setStatus($openStatus);

    $closedProject = Project::factory()->create(['title' => 'Closed rollout', 'owner_user_id' => $user->id]);
    $closedProject->setStatus(Status::factory()->closed()->create(['slug' => 'completed']));

    $this->actingAs($supervisor)
        ->get(route('users.show', ['user' => $user, 'tab' => 'projects']))
        ->assertSuccessful()
        ->assertSee(__('All projects'))
        ->assertSee('Open rollout')
        ->assertSee('Closed rollout');
});

test('tasks tab lists all related tasks for the user', function () {
    $supervisor = User::factory()->create();
    $user = User::factory()->forSupervisor($supervisor)->create(['name' => 'Don']);
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

    $this->actingAs($supervisor)
        ->get(route('users.show', ['user' => $user, 'tab' => 'tasks']))
        ->assertSuccessful()
        ->assertSee(__('All tasks'))
        ->assertSee('Open checklist item')
        ->assertSee('Closed checklist item');
});

test('users who cannot see a profile receive not found', function () {
    $viewer = User::factory()->create();
    $other = User::factory()->create(['name' => 'Hidden User']);

    $this->actingAs($viewer)
        ->get(route('users.show', $other))
        ->assertNotFound();
});
