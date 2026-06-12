<?php

use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot view a project', function () {
    $project = Project::factory()->teamOwned()->create();

    $this->get(route('projects.show', $project))
        ->assertRedirect(route('login'));
});

test('team members can view a project and its tasks', function () {
    $member = User::factory()->create();
    $assignee = User::factory()->create(['name' => 'Jamie Lee']);
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($member);
    $status = Status::factory()->create(['slug' => 'pending', 'is_closed' => false, 'name' => 'Planning']);

    $project = Project::factory()->teamOwned()->create([
        'title' => 'Platform rollout',
        'description' => 'Ship the next platform release.',
    ]);
    $project->syncTeams([$team]);
    $project->setStatus($status);

    $task = Task::factory()->forProject($project)->create(['title' => 'Prepare launch checklist']);
    $task->syncAssignees([$assignee]);

    $this->actingAs($member)
        ->get(route('projects.show', $project))
        ->assertSuccessful()
        ->assertSee('Platform rollout')
        ->assertSee('Ship the next platform release.')
        ->assertSee('Platform Team')
        ->assertSee('Planning')
        ->assertSee('Prepare launch checklist')
        ->assertSee('Jamie Lee');
});

test('users cannot view personally owned projects they do not own', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();

    $project = Project::factory()->personallyOwnedBy($owner)->create([
        'title' => 'Private portfolio',
    ]);

    $this->actingAs($viewer)
        ->get(route('projects.show', $project))
        ->assertNotFound();
});

test('owners can view their personally owned projects', function () {
    $owner = User::factory()->create(['name' => 'Project Owner']);

    $project = Project::factory()->personallyOwnedBy($owner)->create([
        'title' => 'Private portfolio',
    ]);

    $this->actingAs($owner)
        ->get(route('projects.show', $project))
        ->assertSuccessful()
        ->assertSee('Private portfolio')
        ->assertSee('Project Owner');
});
