<?php

use App\Models\Checklist;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('private user owned checklists are hidden from other users', function () {
    $owner = User::factory()->create(['name' => 'Checklist Owner']);
    $viewer = User::factory()->create();

    Checklist::factory()->ownedBy($owner)->create([
        'title' => 'Private personal checklist',
        'visibility' => 'private',
    ]);

    $this->actingAs($viewer)
        ->get(route('checklists.index'))
        ->assertSuccessful()
        ->assertDontSee('Private personal checklist')
        ->assertDontSee('Checklist Owner');
});

test('owners can see their private user owned checklists', function () {
    $owner = User::factory()->create(['name' => 'Checklist Owner']);

    Checklist::factory()->ownedBy($owner)->create([
        'title' => 'Private personal checklist',
        'visibility' => 'private',
    ]);

    $this->actingAs($owner)
        ->get(route('checklists.index'))
        ->assertSuccessful()
        ->assertSee('Private personal checklist')
        ->assertSee('Checklist Owner');
});

test('project owned checklists follow project visibility', function () {
    $member = User::factory()->create();
    $viewer = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Delivery Team']);
    $team->members()->attach($member);

    $project = Project::factory()->privateForTeam($member)->create([
        'title' => 'Private project',
    ]);
    $project->syncTeams([$team]);

    Checklist::factory()->ownedBy($project)->create([
        'title' => 'Project checklist',
        'visibility' => 'private',
    ]);

    $this->actingAs($member)
        ->get(route('checklists.index'))
        ->assertSuccessful()
        ->assertSee('Project checklist')
        ->assertSee('Private project');

    $this->actingAs($viewer)
        ->get(route('checklists.index'))
        ->assertSuccessful()
        ->assertDontSee('Project checklist')
        ->assertDontSee('Private project');
});
