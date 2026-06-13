<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('team leaders can add a user to the team', function () {
    $leader = User::factory()->create();
    $newMember = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->addMember($leader, TeamRole::Leader);

    $this->actingAs($leader)
        ->post(route('teams.members.store', $team), [
            'user_id' => $newMember->id,
            'role' => TeamRole::Member->value,
        ])
        ->assertRedirect(route('teams.show', ['team' => $team, 'tab' => 'members']))
        ->assertSessionHas('status');

    expect($team->members()->pluck('users.id')->all())->toContain($leader->id, $newMember->id)
        ->and($team->roleFor($newMember))->toBe(TeamRole::Member);
});

test('team leaders can remove a user from the team', function () {
    $leader = User::factory()->create();
    $otherMember = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->addMember($leader, TeamRole::Leader);
    $team->addMember($otherMember, TeamRole::Member);

    $this->actingAs($leader)
        ->delete(route('teams.members.destroy', [$team, $otherMember]))
        ->assertRedirect(route('teams.show', ['team' => $team, 'tab' => 'members']))
        ->assertSessionHas('status');

    expect($team->members()->pluck('users.id')->all())->toBe([$leader->id]);
});

test('team leaders can update a member role', function () {
    $leader = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->addMember($leader, TeamRole::Leader);
    $team->addMember($member, TeamRole::Member);

    $this->actingAs($leader)
        ->patch(route('teams.members.role.update', [$team, $member]), [
            'role' => TeamRole::Leader->value,
        ])
        ->assertRedirect(route('teams.show', ['team' => $team, 'tab' => 'members']))
        ->assertSessionHas('status');

    expect($team->roleFor($member))->toBe(TeamRole::Leader);
});

test('team members cannot add or remove users', function () {
    $member = User::factory()->create();
    $candidate = User::factory()->create();
    $otherMember = User::factory()->create();
    $team = Team::factory()->create();
    $team->addMember($member, TeamRole::Member);
    $team->addMember($otherMember, TeamRole::Member);

    $this->actingAs($member)
        ->post(route('teams.members.store', $team), [
            'user_id' => $candidate->id,
        ])
        ->assertForbidden();

    $this->actingAs($member)
        ->delete(route('teams.members.destroy', [$team, $otherMember]))
        ->assertForbidden();

    expect($team->members()->pluck('users.id')->all())->toContain($member->id, $otherMember->id);
});

test('users who are not team members cannot add team members', function () {
    $leader = User::factory()->create();
    $outsider = User::factory()->create();
    $candidate = User::factory()->create();
    $team = Team::factory()->create();
    $team->addMember($leader, TeamRole::Leader);

    $this->actingAs($outsider)
        ->post(route('teams.members.store', $team), [
            'user_id' => $candidate->id,
        ])
        ->assertNotFound();

    expect($team->members()->pluck('users.id')->all())->toBe([$leader->id]);
});

test('users who are not team members cannot remove team members', function () {
    $leader = User::factory()->create();
    $otherMember = User::factory()->create();
    $outsider = User::factory()->create();
    $team = Team::factory()->create();
    $team->addMember($leader, TeamRole::Leader);
    $team->addMember($otherMember, TeamRole::Member);

    $this->actingAs($outsider)
        ->delete(route('teams.members.destroy', [$team, $otherMember]))
        ->assertNotFound();

    expect($team->members()->pluck('users.id')->all())->toContain($leader->id, $otherMember->id);
});

test('team leaders cannot add an existing member again', function () {
    $leader = User::factory()->create();
    $team = Team::factory()->create();
    $team->addMember($leader, TeamRole::Leader);

    $this->actingAs($leader)
        ->post(route('teams.members.store', $team), [
            'user_id' => $leader->id,
        ])
        ->assertSessionHasErrors('user_id');

    expect($team->members()->count())->toBe(1);
});

test('super admins can add a user to any team without being a member', function () {
    $admin = User::factory()->superAdmin()->create();
    $member = User::factory()->create();
    $candidate = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->addMember($member, TeamRole::Member);

    $this->actingAs($admin)
        ->post(route('teams.members.store', $team), [
            'user_id' => $candidate->id,
            'role' => TeamRole::Leader->value,
        ])
        ->assertRedirect(route('teams.show', ['team' => $team, 'tab' => 'members']))
        ->assertSessionHas('status');

    expect($team->members()->pluck('users.id')->all())->toContain($member->id, $candidate->id)
        ->and($team->roleFor($candidate))->toBe(TeamRole::Leader);
});

test('super admins can remove a user from any team without being a member', function () {
    $admin = User::factory()->superAdmin()->create();
    $member = User::factory()->create();
    $otherMember = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->addMember($member, TeamRole::Member);
    $team->addMember($otherMember, TeamRole::Member);

    $this->actingAs($admin)
        ->delete(route('teams.members.destroy', [$team, $otherMember]))
        ->assertRedirect(route('teams.show', ['team' => $team, 'tab' => 'members']))
        ->assertSessionHas('status');

    expect($team->members()->pluck('users.id')->all())->toBe([$member->id]);
});

test('teams cannot remove their last leader', function () {
    $leader = User::factory()->create();
    $team = Team::factory()->create();
    $team->addMember($leader, TeamRole::Leader);

    $this->actingAs($leader)
        ->delete(route('teams.members.destroy', [$team, $leader]))
        ->assertRedirect(route('teams.show', ['team' => $team, 'tab' => 'members']))
        ->assertSessionHasErrors('member');

    expect($team->members()->whereKey($leader->id)->exists())->toBeTrue();
});

test('teams cannot demote their last leader', function () {
    $leader = User::factory()->create();
    $team = Team::factory()->create();
    $team->addMember($leader, TeamRole::Leader);

    $this->actingAs($leader)
        ->patch(route('teams.members.role.update', [$team, $leader]), [
            'role' => TeamRole::Member->value,
        ])
        ->assertRedirect(route('teams.show', ['team' => $team, 'tab' => 'members']))
        ->assertSessionHasErrors('role.'.$leader->id);

    expect($team->roleFor($leader))->toBe(TeamRole::Leader);
});

test('team leaders can update a member role via json', function () {
    $leader = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();
    $team->addMember($leader, TeamRole::Leader);
    $team->addMember($member, TeamRole::Member);

    $this->actingAs($leader)
        ->patchJson(route('teams.members.role.update', [$team, $member]), [
            'role' => TeamRole::Leader->value,
        ])
        ->assertSuccessful()
        ->assertJsonPath('member.id', $member->id)
        ->assertJsonPath('member.role', TeamRole::Leader->value);

    expect($team->roleFor($member))->toBe(TeamRole::Leader);
});

test('team leaders can remove a member via json', function () {
    $leader = User::factory()->create();
    $otherMember = User::factory()->create();
    $team = Team::factory()->create();
    $team->addMember($leader, TeamRole::Leader);
    $team->addMember($otherMember, TeamRole::Member);

    $this->actingAs($leader)
        ->deleteJson(route('teams.members.destroy', [$team, $otherMember]))
        ->assertSuccessful()
        ->assertJsonPath('member_id', $otherMember->id)
        ->assertJsonPath('member_count', 1);

    expect($team->members()->pluck('users.id')->all())->toBe([$leader->id]);
});
