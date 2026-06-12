<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('team members can update team membership', function () {
    $member = User::factory()->create();
    $newMember = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($member);

    $this->actingAs($member)
        ->put(route('teams.members.update', $team), [
            'user_ids' => [$member->id, $newMember->id],
        ])
        ->assertRedirect(route('teams.show', ['team' => $team, 'tab' => 'members']))
        ->assertSessionHas('status');

    expect($team->members()->pluck('users.id')->all())
        ->toContain($member->id, $newMember->id);
});

test('users who are not team members cannot update team membership', function () {
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $candidate = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($member);

    $this->actingAs($outsider)
        ->put(route('teams.members.update', $team), [
            'user_ids' => [$candidate->id],
        ])
        ->assertForbidden();

    expect($team->members()->pluck('users.id')->all())->toBe([$member->id]);
});
