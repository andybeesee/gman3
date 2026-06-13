<?php

use App\Models\Checklist;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot view the checklist index', function () {
    $this->get(route('checklists.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view public checklists', function () {
    $viewer = User::factory()->create();
    $owner = User::factory()->create(['name' => 'Alex Rivera']);
    $checklist = Checklist::factory()->ownedBy($owner)->create([
        'title' => 'Public launch checklist',
        'visibility' => 'public',
    ]);

    Task::query()->create([
        'title' => 'Nested task',
        'start_date' => '2026-05-01 09:00:00',
        'due_date' => '2026-05-03 17:00:00',
        'checklist_id' => $checklist->id,
        'checklist_position' => 1,
        'visibility' => 'public',
    ])->setOwner($owner);

    $this->actingAs($viewer)
        ->get(route('checklists.index'))
        ->assertSuccessful()
        ->assertSee('Public launch checklist')
        ->assertSee('Alex Rivera')
        ->assertSee('May 1, 2026')
        ->assertSee('May 3, 2026')
        ->assertSee('1', false);
});

test('team members can view private team checklists', function () {
    $viewer = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($viewer);

    $checklist = Checklist::factory()->ownedBy($team)->create([
        'title' => 'Team launch checklist',
    ]);
    $checklist->syncTeams([$team]);

    $this->actingAs($viewer)
        ->get(route('checklists.index'))
        ->assertSuccessful()
        ->assertSee('Team launch checklist')
        ->assertSee('Platform Team');
});
