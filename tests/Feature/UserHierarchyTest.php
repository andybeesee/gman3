<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('users do not see the hierarchy tab on a user profile', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create(['name' => 'Roger']);

    $this->actingAs($admin)
        ->get(route('users.show', $user))
        ->assertSuccessful()
        ->assertDontSee(__('Hierarchy'));
});

test('hierarchy tab requests fall back to the dashboard', function () {
    $admin = User::factory()->superAdmin()->create();
    $roger = User::factory()->create(['name' => 'Roger', 'email' => 'roger@example.com']);

    $this->actingAs($admin)
        ->get(route('users.show', ['user' => $roger, 'tab' => 'hierarchy']))
        ->assertSuccessful()
        ->assertSee(__('Dashboard'), false)
        ->assertDontSee(__('Hierarchy'));
});

test('subordinate management route is removed', function () {
    $admin = User::factory()->superAdmin()->create();
    $roger = User::factory()->create(['name' => 'Roger']);
    $don = User::factory()->create(['name' => 'Don']);

    $this->actingAs($admin)
        ->delete("/users/{$roger->id}/subordinates/{$don->id}")
        ->assertNotFound();
});
