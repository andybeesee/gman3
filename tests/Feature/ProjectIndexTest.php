<?php

use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot view the project index', function () {
    $this->get(route('projects.index'))
        ->assertRedirect(route('login'));
});

test('team members can view team owned projects on the index', function () {
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($member);
    $status = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);

    $project = Project::factory()->teamOwned()->create(['title' => 'Platform rollout']);
    $project->syncTeams([$team]);
    $project->setStatus($status);

    Task::factory()->count(3)->forProject($project)->create();

    $this->actingAs($member)
        ->get(route('projects.index'))
        ->assertSuccessful()
        ->assertSee('Platform rollout')
        ->assertSee(__('Owners'))
        ->assertSee('Platform Team')
        ->assertSee('3', false);
});

test('users cannot see personally owned projects they do not own', function () {
    $owner = User::factory()->create(['name' => 'Project Owner']);
    $viewer = User::factory()->create();

    $project = Project::factory()->personallyOwnedBy($owner)->create([
        'title' => 'Private portfolio',
    ]);
    $project->setStatus(Status::factory()->create(['slug' => 'pending', 'is_closed' => false]));

    $this->actingAs($viewer)
        ->get(route('projects.index'))
        ->assertSuccessful()
        ->assertDontSee('Private portfolio')
        ->assertDontSee('Project Owner');
});

test('owners can see their personally owned projects on the index', function () {
    $owner = User::factory()->create(['name' => 'Project Owner']);
    $status = Status::factory()->create(['slug' => 'pending', 'is_closed' => false, 'name' => 'Planning']);

    $project = Project::factory()->personallyOwnedBy($owner)->create([
        'title' => 'Private portfolio',
    ]);
    $project->setStatus($status);

    $this->actingAs($owner)
        ->get(route('projects.index'))
        ->assertSuccessful()
        ->assertSee('Private portfolio')
        ->assertSee(__('Owners'))
        ->assertSee('Project Owner')
        ->assertSee('Planning');
});
