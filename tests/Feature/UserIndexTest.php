<?php

use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot view the user index', function () {
    $this->get(route('users.index'))
        ->assertRedirect(route('login'));
});

test('users only see themselves when they have no reports', function () {
    $viewer = User::factory()->create(['name' => 'Isolated User']);
    $other = User::factory()->create(['name' => 'Hidden User']);

    $this->actingAs($viewer)
        ->get(route('users.index'))
        ->assertSuccessful()
        ->assertSee('Isolated User')
        ->assertDontSee('Hidden User');
});

test('supervisors see themselves and all descendants on the user index', function () {
    $roger = User::factory()->create(['name' => 'Roger']);
    $don = User::factory()->forSupervisor($roger)->create(['name' => 'Don']);
    $peggy = User::factory()->forSupervisor($don)->create(['name' => 'Peggy']);
    $stranger = User::factory()->create(['name' => 'Stranger']);

    $this->actingAs($roger)
        ->get(route('users.index'))
        ->assertSuccessful()
        ->assertSee('Roger')
        ->assertSee('Don')
        ->assertSee('Peggy')
        ->assertDontSee('Stranger');
});

test('user index shows open assigned task counts', function () {
    $user = User::factory()->create(['name' => 'Jamie Lee']);
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'completed']);

    $openTask = Task::query()->create(['title' => 'Open assigned task']);
    $openTask->setOwner($user);
    $openTask->syncAssignees([$user]);
    $openTask->setStatus($openStatus);

    $closedTask = Task::query()->create(['title' => 'Closed assigned task']);
    $closedTask->setOwner($user);
    $closedTask->syncAssignees([$user]);
    $closedTask->setStatus($closedStatus);

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertSuccessful()
        ->assertSee('Jamie Lee')
        ->assertSee('1', false);
});
