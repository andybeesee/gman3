<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('team members can add a user to the team', function () {
    $member = User::factory()->create();
    $newMember = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($member);

    $this->actingAs($member)
        ->post(route('teams.members.store', $team), [
            'user_id' => $newMember->id,
        ])
        ->assertRedirect(route('teams.show', ['team' => $team, 'tab' => 'members']))
        ->assertSessionHas('status');

    expect($team->members()->pluck('users.id')->all())->toContain($member->id, $newMember->id);
});

test('team members can remove a user from the team', function () {
    $member = User::factory()->create();
    $otherMember = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach([$member->id, $otherMember->id]);

    $this->actingAs($member)
        ->delete(route('teams.members.destroy', [$team, $otherMember]))
        ->assertRedirect(route('teams.show', ['team' => $team, 'tab' => 'members']))
        ->assertSessionHas('status');

    expect($team->members()->pluck('users.id')->all())->toBe([$member->id]);
});

test('users who are not team members cannot add team members', function () {
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $candidate = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($member);

    $this->actingAs($outsider)
        ->post(route('teams.members.store', $team), [
            'user_id' => $candidate->id,
        ])
        ->assertNotFound();

    expect($team->members()->pluck('users.id')->all())->toBe([$member->id]);
});

test('users who are not team members cannot remove team members', function () {
    $member = User::factory()->create();
    $otherMember = User::factory()->create();
    $outsider = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach([$member->id, $otherMember->id]);

    $this->actingAs($outsider)
        ->delete(route('teams.members.destroy', [$team, $otherMember]))
        ->assertNotFound();

    expect($team->members()->pluck('users.id')->all())->toContain($member->id, $otherMember->id);
});

test('team members cannot add an existing member again', function () {
    $member = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($member);

    $this->actingAs($member)
        ->post(route('teams.members.store', $team), [
            'user_id' => $member->id,
        ])
        ->assertSessionHasErrors('user_id');

    expect($team->members()->count())->toBe(1);
});
