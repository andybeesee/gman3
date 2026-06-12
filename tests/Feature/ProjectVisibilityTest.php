<?php

use App\Enums\Visibility;
use App\Models\Project;
use App\Models\Status;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('private user owned projects are only visible to the owner', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $project = Project::factory()->privateForUser($owner)->create([
        'title' => 'Private project',
    ]);
    $project->setStatus(Status::factory()->create(['slug' => 'pending', 'is_closed' => false]));

    $ownerVisibleIds = Project::query()->visibleTo($owner)->pluck('id');
    $otherVisibleIds = Project::query()->visibleTo($otherUser)->pluck('id');

    expect($ownerVisibleIds)->toContain($project->id)
        ->and($otherVisibleIds)->not->toContain($project->id)
        ->and($project->visibility)->toBe(Visibility::Private);
});

test('private team owned projects are visible to members of associated teams', function () {
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($member);

    $project = Project::factory()->privateForTeam()->create([
        'title' => 'Shared project',
    ]);
    $project->syncTeams([$team]);
    $project->setStatus(Status::factory()->create(['slug' => 'pending', 'is_closed' => false]));

    $memberVisibleIds = Project::query()->visibleTo($member)->pluck('id');
    $outsiderVisibleIds = Project::query()->visibleTo($outsider)->pluck('id');

    expect($memberVisibleIds)->toContain($project->id)
        ->and($outsiderVisibleIds)->not->toContain($project->id)
        ->and($project->visibility)->toBe(Visibility::Private);
});

test('public projects are visible to every authenticated user', function () {
    $creator = User::factory()->create();
    $viewer = User::factory()->create();

    $project = Project::factory()->public($creator)->create([
        'title' => 'Company wide project',
    ]);

    expect(Project::query()->visibleTo($viewer)->pluck('id'))->toContain($project->id)
        ->and($viewer->can('view', $project))->toBeTrue();
});

test('private resources can grant access to other users and teams', function () {
    $owner = User::factory()->create();
    $grantedUser = User::factory()->create();
    $grantedMember = User::factory()->create();
    $outsider = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($grantedMember);

    $project = Project::factory()->privateForUser($owner)->create([
        'title' => 'Shared privately',
    ]);

    $project->grantAccessTo($grantedUser);
    $project->grantAccessTo($team);

    expect($project->isVisibleTo($grantedUser))->toBeTrue()
        ->and($project->isVisibleTo($grantedMember))->toBeTrue()
        ->and($project->isVisibleTo($outsider))->toBeFalse();
});

test('project policy mirrors visibility rules', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $project = Project::factory()->privateForUser($owner)->create();

    expect($owner->can('view', $project))->toBeTrue()
        ->and($otherUser->can('view', $project))->toBeFalse();
});

test('projects store status changes using the project morph alias', function () {
    $owner = User::factory()->create();
    $status = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);

    $project = Project::factory()->privateForUser($owner)->create();
    $project->setStatus($status, $owner);

    expect($project->fresh()->status?->is($status))->toBeTrue()
        ->and($project->statusChanges()->value('statusable_type'))->toBe('project');
});
