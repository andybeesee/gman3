<?php

use App\Enums\ProjectOwnership;
use App\Models\Project;
use App\Models\Status;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('personally owned projects are only visible to the owner', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $project = Project::factory()->personallyOwnedBy($owner)->create([
        'title' => 'Private project',
    ]);
    $project->setStatus(Status::factory()->create(['slug' => 'pending', 'is_closed' => false]));

    $ownerVisibleIds = Project::query()->visibleTo($owner)->pluck('id');
    $otherVisibleIds = Project::query()->visibleTo($otherUser)->pluck('id');

    expect($ownerVisibleIds)->toContain($project->id)
        ->and($otherVisibleIds)->not->toContain($project->id)
        ->and($project->ownership)->toBe(ProjectOwnership::User);
});

test('team owned projects are visible to members of associated teams', function () {
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($member);

    $project = Project::factory()->teamOwned()->create([
        'title' => 'Shared project',
    ]);
    $project->syncTeams([$team]);
    $team->members()->sync([$member->id]);
    $project->setStatus(Status::factory()->create(['slug' => 'pending', 'is_closed' => false]));

    $memberVisibleIds = Project::query()->visibleTo($member)->pluck('id');
    $outsiderVisibleIds = Project::query()->visibleTo($outsider)->pluck('id');

    expect($memberVisibleIds)->toContain($project->id)
        ->and($outsiderVisibleIds)->not->toContain($project->id)
        ->and($project->ownership)->toBe(ProjectOwnership::Team);
});

test('project policy mirrors ownership visibility rules', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $project = Project::factory()->personallyOwnedBy($owner)->create();

    expect($owner->can('view', $project))->toBeTrue()
        ->and($otherUser->can('view', $project))->toBeFalse();
});

test('projects store status changes using the project morph alias', function () {
    $owner = User::factory()->create();
    $status = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);

    $project = Project::factory()->personallyOwnedBy($owner)->create();
    $project->setStatus($status, $owner);

    expect($project->fresh()->status?->is($status))->toBeTrue()
        ->and($project->statusChanges()->value('statusable_type'))->toBe('project');
});
