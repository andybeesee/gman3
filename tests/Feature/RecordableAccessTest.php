<?php

use App\Enums\Visibility;
use App\Models\Checklist;
use App\Models\Project;
use App\Models\Record;
use App\Models\Team;
use App\Models\User;
use App\Queries\RecordableQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('recordable models create and sync their record rows', function () {
    $owner = User::factory()->create();

    $project = Project::factory()->privateForUser($owner)->create([
        'title' => 'Original project name',
    ]);

    expect($project->record)
        ->not->toBeNull()
        ->and($project->record->title)->toBe('Original project name')
        ->and($project->record->visibility)->toBe(Visibility::Private)
        ->and($project->record->owner_type)->toBe('user')
        ->and($project->record->owner_id)->toBe($owner->id);

    $project->forceFill([
        'title' => 'Renamed project',
        'visibility' => Visibility::Public,
    ])->save();

    expect($project->record()->first())
        ->title->toBe('Renamed project')
        ->visibility->toBe(Visibility::Public);
});

test('recordable query returns visible models through record access rules', function () {
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($member);

    $teamProject = Project::factory()->privateForTeam($member)->create([
        'title' => 'Team project',
    ]);
    $teamProject->syncTeams([$team]);

    $privateChecklist = Checklist::factory()->ownedBy($outsider)->create([
        'title' => 'Hidden checklist',
        'visibility' => Visibility::Private,
    ]);

    expect(Record::query()->visibleTo($member)->where('recordable_type', 'project')->pluck('recordable_id')->all())
        ->toContain($teamProject->id)
        ->and(Record::query()->visibleTo($member)->where('recordable_type', 'checklist')->pluck('recordable_id')->all())
        ->not->toContain($privateChecklist->id);

    expect(RecordableQuery::projectsVisibleTo($member)->pluck('title')->all())
        ->toContain('Team project');

    expect(RecordableQuery::checklistsVisibleTo($member)->pluck('title')->all())
        ->not->toContain('Hidden checklist');
});
