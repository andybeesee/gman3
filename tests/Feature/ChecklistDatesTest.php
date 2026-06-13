<?php

use App\Models\Checklist;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('checklist dates roll up from ordered tasks', function () {
    $owner = User::factory()->create();
    $checklist = Checklist::factory()->ownedBy($owner)->create([
        'title' => 'Launch readiness',
    ]);

    Task::query()->create([
        'title' => 'Second task',
        'start_date' => '2026-03-10 09:00:00',
        'due_date' => '2026-03-14 17:00:00',
        'checklist_id' => $checklist->id,
        'checklist_position' => 2,
    ])->setOwner($owner);

    Task::query()->create([
        'title' => 'First task',
        'start_date' => '2026-03-01 09:00:00',
        'due_date' => '2026-03-05 17:00:00',
        'checklist_id' => $checklist->id,
        'checklist_position' => 1,
    ])->setOwner($owner);

    $checklist->refresh();

    expect($checklist->start_date?->toDateTimeString())->toBe('2026-03-01 09:00:00')
        ->and($checklist->due_date?->toDateTimeString())->toBe('2026-03-14 17:00:00')
        ->and($checklist->tasks->pluck('title')->all())->toBe([
            'First task',
            'Second task',
        ]);
});

test('moving a task between checklists resyncs both date rollups', function () {
    $owner = User::factory()->create();
    $source = Checklist::factory()->ownedBy($owner)->create(['title' => 'Source']);
    $target = Checklist::factory()->ownedBy($owner)->create(['title' => 'Target']);

    $task = Task::query()->create([
        'title' => 'Move me',
        'start_date' => '2026-04-01 09:00:00',
        'due_date' => '2026-04-08 17:00:00',
        'checklist_id' => $source->id,
        'checklist_position' => 1,
    ]);
    $task->setOwner($owner);

    $task->update([
        'checklist_id' => $target->id,
        'checklist_position' => 1,
    ]);

    expect($source->fresh()->start_date)->toBeNull()
        ->and($source->fresh()->due_date)->toBeNull()
        ->and($target->fresh()->start_date?->toDateTimeString())->toBe('2026-04-01 09:00:00')
        ->and($target->fresh()->due_date?->toDateTimeString())->toBe('2026-04-08 17:00:00');
});
