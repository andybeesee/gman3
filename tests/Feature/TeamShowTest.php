<?php

use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot view a team', function () {
    $team = Team::factory()->create();

    $this->get(route('teams.show', $team))
        ->assertRedirect(route('login'));
});

test('dashboard tab shows active projects tasks and members', function () {
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($member);
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false, 'name' => 'Planning']);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'completed']);

    $project = Project::factory()->teamOwned()->create(['title' => 'Platform rollout']);
    $project->syncTeams([$team]);
    $project->setStatus($openStatus);

    $closedProject = Project::factory()->teamOwned()->create(['title' => 'Archived rollout']);
    $closedProject->syncTeams([$team]);
    $closedProject->setStatus($closedStatus);

    $openTask = Task::query()->create(['title' => 'Prepare launch checklist']);
    $openTask->setOwner($team);
    $openTask->syncTeams([$team]);
    $openTask->syncAssignees([$member]);
    $openTask->setStatus($openStatus);

    $closedTask = Task::query()->create(['title' => 'Archived checklist item']);
    $closedTask->setOwner($team);
    $closedTask->syncTeams([$team]);
    $closedTask->setStatus($closedStatus);

    $this->actingAs($member)
        ->get(route('teams.show', $team))
        ->assertSuccessful()
        ->assertSee(__('Dashboard'), false)
        ->assertSee('Platform Team')
        ->assertSee($member->name)
        ->assertSee('Platform rollout')
        ->assertSee('Planning')
        ->assertSee('Prepare launch checklist')
        ->assertDontSee('Archived rollout')
        ->assertDontSee('Archived checklist item');
});

test('projects tab lists all visible projects for the team', function () {
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($member);
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'completed']);

    $openProject = Project::factory()->teamOwned()->create(['title' => 'Open rollout']);
    $openProject->syncTeams([$team]);
    $openProject->setStatus($openStatus);

    $closedProject = Project::factory()->teamOwned()->create(['title' => 'Closed rollout']);
    $closedProject->syncTeams([$team]);
    $closedProject->setStatus($closedStatus);

    $this->actingAs($member)
        ->get(route('teams.show', ['team' => $team, 'tab' => 'projects']))
        ->assertSuccessful()
        ->assertSee(__('All projects'))
        ->assertSee('Open rollout')
        ->assertSee('Closed rollout');
});

test('tasks tab lists all visible tasks for the team', function () {
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($member);
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'completed']);

    $openTask = Task::query()->create(['title' => 'Open checklist item']);
    $openTask->setOwner($team);
    $openTask->syncTeams([$team]);
    $openTask->setStatus($openStatus);

    $closedTask = Task::query()->create(['title' => 'Closed checklist item']);
    $closedTask->setOwner($team);
    $closedTask->syncTeams([$team]);
    $closedTask->setStatus($closedStatus);

    $this->actingAs($member)
        ->get(route('teams.show', ['team' => $team, 'tab' => 'tasks']))
        ->assertSuccessful()
        ->assertSee(__('All tasks'))
        ->assertSee('Open checklist item')
        ->assertSee('Closed checklist item');
});

test('members tab shows an edit form for team members', function () {
    $member = User::factory()->create(['name' => 'Jamie Lee']);
    $otherUser = User::factory()->create(['name' => 'Alex Rivera']);
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($member);

    $this->actingAs($member)
        ->get(route('teams.show', ['team' => $team, 'tab' => 'members']))
        ->assertSuccessful()
        ->assertSee(__('Save members'))
        ->assertSee('Jamie Lee')
        ->assertSee('Alex Rivera');
});

test('users who are not team members do not see team owned projects on the dashboard', function () {
    $member = User::factory()->create(['name' => 'Team Member']);
    $viewer = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($member);

    $project = Project::factory()->teamOwned()->create(['title' => 'Hidden platform rollout']);
    $project->syncTeams([$team]);
    $project->setStatus(Status::factory()->create(['slug' => 'pending', 'is_closed' => false]));

    $this->actingAs($viewer)
        ->get(route('teams.show', $team))
        ->assertSuccessful()
        ->assertSee('Team Member')
        ->assertDontSee('Hidden platform rollout');
});

test('projects tab shows an empty state when no visible projects exist', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Empty Team']);

    $this->actingAs($user)
        ->get(route('teams.show', ['team' => $team, 'tab' => 'projects']))
        ->assertSuccessful()
        ->assertSee('Empty Team')
        ->assertSee(__('No projects are available for this team yet.'));
});
