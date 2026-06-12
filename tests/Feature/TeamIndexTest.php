<?php

use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot view the team index', function () {
    $this->get(route('teams.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view teams they belong to with member and open task counts', function () {
    $user = User::factory()->create();
    $memberOne = User::factory()->create();
    $memberTwo = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach([$user->id, $memberOne->id, $memberTwo->id]);

    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'completed']);

    $openTask = Task::query()->create(['title' => 'Open team task']);
    $openTask->setOwner($team);
    $openTask->syncTeams([$team]);
    $openTask->setStatus($openStatus);

    $closedTask = Task::query()->create(['title' => 'Closed team task']);
    $closedTask->setOwner($team);
    $closedTask->syncTeams([$team]);
    $closedTask->setStatus($closedStatus);

    $this->actingAs($user)
        ->get(route('teams.index'))
        ->assertSuccessful()
        ->assertSee('Platform Team')
        ->assertSee('2', false)
        ->assertSee('1', false)
        ->assertDontSee('Closed team task');
});

test('team index shows public teams to every authenticated user', function () {
    $user = User::factory()->create();
    Team::factory()->public()->create(['name' => 'Empty Team']);

    $this->actingAs($user)
        ->get(route('teams.index'))
        ->assertSuccessful()
        ->assertSee('Empty Team')
        ->assertSee('0', false);
});
