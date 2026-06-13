<?php

use App\Enums\Visibility;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('project tables show public and private visibility indicators with share details', function () {
    $owner = User::factory()->create();
    $sharedUser = User::factory()->create(['name' => 'Alex Rivera']);
    $sharedTeam = Team::factory()->create(['name' => 'Platform Team']);

    Project::factory()->public($owner)->create(['title' => 'Public rollout']);

    $privateProject = Project::factory()->privateForUser($owner)->create(['title' => 'Private rollout']);
    $privateProject->grantAccessTo($sharedUser);
    $privateProject->grantAccessTo($sharedTeam);

    $this->actingAs($owner)
        ->get(route('projects.index'))
        ->assertSuccessful()
        ->assertSee('Public rollout')
        ->assertSee('Private rollout')
        ->assertSee('fa-globe', false)
        ->assertSee('title="Public to everyone in the company."', false)
        ->assertSee('fa-lock', false)
        ->assertSee('title="Private. Shared with: Team: Platform Team, User: Alex Rivera"', false);
});

test('task tables show private visibility indicators without explicit shares', function () {
    $owner = User::factory()->create();

    $task = Task::query()->create([
        'title' => 'Private checklist',
        'visibility' => Visibility::Private,
    ]);
    $task->setOwner($owner);
    $task->syncAssignees([$owner]);

    $this->actingAs($owner)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertSee('Private checklist')
        ->assertSee('fa-lock', false)
        ->assertSee('title="Private. No explicit shares."', false);
});
