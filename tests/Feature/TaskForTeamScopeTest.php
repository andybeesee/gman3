<?php

use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('for team scope includes linked team owned and team owned project tasks', function () {
    $team = Team::factory()->create();
    $otherTeam = Team::factory()->create();
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);

    $linkedTask = Task::query()->create(['title' => 'Linked task']);
    $linkedTask->setOwner($team);
    $linkedTask->syncTeams([$team]);
    $linkedTask->setStatus($openStatus);

    $ownedTask = Task::query()->create(['title' => 'Owned task']);
    $ownedTask->setOwner($team);
    $ownedTask->setStatus($openStatus);

    $project = Project::factory()->teamOwned()->create();
    $project->syncTeams([$team]);

    $projectTask = Task::query()->create(['title' => 'Project task']);
    $projectTask->setOwner($project);
    $projectTask->setStatus($openStatus);

    $otherTeamTask = Task::query()->create(['title' => 'Other team task']);
    $otherTeamTask->setOwner($otherTeam);
    $otherTeamTask->syncTeams([$otherTeam]);
    $otherTeamTask->setStatus($openStatus);

    expect(Task::query()->forTeam($team)->pluck('title')->all())
        ->toContain('Linked task', 'Owned task', 'Project task')
        ->not->toContain('Other team task');
});

test('for team scope excludes tasks from user owned projects even when linked to the team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);

    $project = Project::factory()->privateForUser($user)->create();
    $project->syncTeams([$team]);

    $projectTask = Task::query()->create(['title' => 'Personal project task']);
    $projectTask->setOwner($project);
    $projectTask->setStatus($openStatus);

    expect(Task::query()->forTeam($team)->pluck('title')->all())
        ->not->toContain('Personal project task');
});
