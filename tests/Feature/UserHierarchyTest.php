<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('super admins see the hierarchy tab on a user profile', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create(['name' => 'Roger']);

    $this->actingAs($admin)
        ->get(route('users.show', $user))
        ->assertSuccessful()
        ->assertSee(__('Hierarchy'));
});

test('non super admins do not see the hierarchy tab on a user profile', function () {
    $viewer = User::factory()->create(['name' => 'Roger']);

    $this->actingAs($viewer)
        ->get(route('users.show', $viewer))
        ->assertSuccessful()
        ->assertDontSee(__('Hierarchy'));
});

test('hierarchy tab shows the supervisor and direct reports', function () {
    $admin = User::factory()->superAdmin()->create();
    $boss = User::factory()->create(['name' => 'Boss', 'email' => 'boss@example.com']);
    $roger = User::factory()->forSupervisor($boss)->create(['name' => 'Roger', 'email' => 'roger@example.com']);
    $don = User::factory()->forSupervisor($roger)->create(['name' => 'Don', 'email' => 'don@example.com']);

    $this->actingAs($admin)
        ->get(route('users.show', ['user' => $roger, 'tab' => 'hierarchy']))
        ->assertSuccessful()
        ->assertSee(__('Supervisor'))
        ->assertSee('Boss')
        ->assertSee('boss@example.com')
        ->assertSee(__('Supervises'))
        ->assertSee('Don')
        ->assertSee('don@example.com')
        ->assertSee(__('Remove'));
});

test('hierarchy tab shows an empty supervisor state when none is assigned', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create(['name' => 'Roger']);

    $this->actingAs($admin)
        ->get(route('users.show', ['user' => $user, 'tab' => 'hierarchy']))
        ->assertSuccessful()
        ->assertSee(__('No supervisor assigned.'));
});

test('super admins can remove a direct report from a supervisor', function () {
    $admin = User::factory()->superAdmin()->create();
    $roger = User::factory()->create(['name' => 'Roger']);
    $don = User::factory()->forSupervisor($roger)->create(['name' => 'Don']);

    $this->actingAs($admin)
        ->delete(route('users.subordinates.destroy', [$roger, $don]))
        ->assertRedirect(route('users.show', ['user' => $roger, 'tab' => 'hierarchy']))
        ->assertSessionHas('status');

    expect($don->fresh()->supervisor_id)->toBeNull();
});

test('non super admins cannot remove a direct report', function () {
    $roger = User::factory()->create(['name' => 'Roger']);
    $don = User::factory()->forSupervisor($roger)->create(['name' => 'Don']);

    $this->actingAs($roger)
        ->delete(route('users.subordinates.destroy', [$roger, $don]))
        ->assertForbidden();

    expect($don->fresh()->supervisor_id)->toBe($roger->id);
});

test('removing a user who is not a direct report returns not found', function () {
    $admin = User::factory()->superAdmin()->create();
    $roger = User::factory()->create(['name' => 'Roger']);
    $don = User::factory()->create(['name' => 'Don']);

    $this->actingAs($admin)
        ->delete(route('users.subordinates.destroy', [$roger, $don]))
        ->assertNotFound();

    expect($don->fresh()->supervisor_id)->toBeNull();
});
