<?php

use App\Enums\Visibility;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('private teams are only visible to members and granted users', function () {
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $grantedUser = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($member);

    expect(Team::query()->visibleTo($member)->pluck('id'))->toContain($team->id)
        ->and(Team::query()->visibleTo($outsider)->pluck('id'))->not->toContain($team->id)
        ->and($team->visibility)->toBe(Visibility::Private);

    $team->grantAccessTo($grantedUser);

    expect(Team::query()->visibleTo($grantedUser)->pluck('id'))->toContain($team->id);
});

test('public teams are visible to every authenticated user', function () {
    $viewer = User::factory()->create();
    $team = Team::factory()->public()->create(['name' => 'Company Team']);

    expect(Team::query()->visibleTo($viewer)->pluck('id'))->toContain($team->id)
        ->and($viewer->can('view', $team))->toBeTrue();
});
