<?php

use App\Models\Checklist;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot view checklist details', function () {
    $checklist = Checklist::factory()->create();

    $this->get(route('checklists.show', $checklist))
        ->assertRedirect(route('login'));
});

test('checklist show page displays tasks in checklist order', function () {
    $owner = User::factory()->create();
    $checklist = Checklist::factory()->ownedBy($owner)->create([
        'title' => 'Launch checklist',
        'visibility' => 'private',
    ]);

    Task::query()->create([
        'title' => 'Second task',
        'checklist_id' => $checklist->id,
        'checklist_position' => 2,
    ])->setOwner($owner);

    Task::query()->create([
        'title' => 'First task',
        'checklist_id' => $checklist->id,
        'checklist_position' => 1,
    ])->setOwner($owner);

    $this->actingAs($owner)
        ->get(route('checklists.show', $checklist))
        ->assertSuccessful()
        ->assertSee('Launch checklist')
        ->assertSeeInOrder(['First task', 'Second task'])
        ->assertSee(route('checklists.tasks.order.update', $checklist), false);
});

test('authorized users can reorder checklist tasks', function () {
    $owner = User::factory()->create();
    $checklist = Checklist::factory()->ownedBy($owner)->create([
        'visibility' => 'private',
    ]);

    $first = Task::query()->create([
        'title' => 'First task',
        'checklist_id' => $checklist->id,
        'checklist_position' => 1,
    ]);
    $first->setOwner($owner);

    $second = Task::query()->create([
        'title' => 'Second task',
        'checklist_id' => $checklist->id,
        'checklist_position' => 2,
    ]);
    $second->setOwner($owner);

    $this->actingAs($owner)
        ->patchJson(route('checklists.tasks.order.update', $checklist), [
            'task_ids' => [$second->id, $first->id],
        ])
        ->assertSuccessful()
        ->assertJson([
            'message' => __('Task order updated.'),
        ]);

    expect($first->fresh()->checklist_position)->toBe(2)
        ->and($second->fresh()->checklist_position)->toBe(1);
});

test('reordering must include every checklist task', function () {
    $owner = User::factory()->create();
    $checklist = Checklist::factory()->ownedBy($owner)->create([
        'visibility' => 'private',
    ]);

    $first = Task::query()->create([
        'title' => 'First task',
        'checklist_id' => $checklist->id,
        'checklist_position' => 1,
    ]);
    $first->setOwner($owner);

    $second = Task::query()->create([
        'title' => 'Second task',
        'checklist_id' => $checklist->id,
        'checklist_position' => 2,
    ]);
    $second->setOwner($owner);

    $this->actingAs($owner)
        ->patchJson(route('checklists.tasks.order.update', $checklist), [
            'task_ids' => [$second->id],
        ])
        ->assertUnprocessable()
        ->assertJsonPath('errors.task_ids.0', __('The task order must include every task in this checklist.'));

    expect($first->fresh()->checklist_position)->toBe(1)
        ->and($second->fresh()->checklist_position)->toBe(2);
});

test('visible non owners cannot reorder public checklist tasks', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $checklist = Checklist::factory()->ownedBy($owner)->create([
        'visibility' => 'public',
    ]);

    $task = Task::query()->create([
        'title' => 'Public task',
        'checklist_id' => $checklist->id,
        'checklist_position' => 1,
        'visibility' => 'public',
    ]);
    $task->setOwner($owner);

    $this->actingAs($viewer)
        ->patchJson(route('checklists.tasks.order.update', $checklist), [
            'task_ids' => [$task->id],
        ])
        ->assertForbidden();
});
