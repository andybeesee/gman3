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

    $projectTask = Task::query()->create(['title' => 'Project launch checklist']);
    $projectTask->setOwner($project);
    $projectTask->setStatus($openStatus);

    $this->actingAs($member)
        ->get(route('teams.show', $team))
        ->assertSuccessful()
        ->assertSee(__('Dashboard'), false)
        ->assertSee('Platform Team')
        ->assertSee($member->name)
        ->assertSee('Platform rollout')
        ->assertSee('Planning')
        ->assertSee('Prepare launch checklist')
        ->assertSee('Project launch checklist')
        ->assertDontSee('Archived rollout')
        ->assertDontSee('Archived checklist item');
});

test('dashboard tab includes open project tasks and team owned tasks without teamables rows', function () {
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($member);
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);

    $project = Project::factory()->teamOwned()->create(['title' => 'Platform rollout']);
    $project->syncTeams([$team]);
    $project->setStatus($openStatus);

    $projectTask = Task::query()->create(['title' => 'Project only task link']);
    $projectTask->setOwner($project);
    $projectTask->setStatus($openStatus);

    $teamTask = Task::query()->create(['title' => 'Team owned without pivot']);
    $teamTask->setOwner($team);
    $teamTask->setStatus($openStatus);

    $this->actingAs($member)
        ->get(route('teams.show', $team))
        ->assertSuccessful()
        ->assertSee('Project only task link')
        ->assertSee('Team owned without pivot');
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

test('tasks tab shows the project for project owned tasks', function () {
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($member);
    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);

    $project = Project::factory()->teamOwned()->create(['title' => 'Platform rollout']);
    $project->syncTeams([$team]);
    $project->setStatus($openStatus);

    $task = Task::query()->create(['title' => 'Prepare launch checklist']);
    $task->setOwner($project);
    $task->setStatus($openStatus);

    $this->actingAs($member)
        ->get(route('teams.show', ['team' => $team, 'tab' => 'tasks']))
        ->assertSuccessful()
        ->assertSee('Prepare launch checklist')
        ->assertSee('Platform rollout');
});

test('members tab shows add and remove controls for team members', function () {
    $member = User::factory()->create(['name' => 'Jamie Lee']);
    $otherUser = User::factory()->forSupervisor($member)->create(['name' => 'Alex Rivera']);
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($member);

    $this->actingAs($member)
        ->get(route('teams.show', ['team' => $team, 'tab' => 'members']))
        ->assertSuccessful()
        ->assertSee(__('Name'))
        ->assertSee(__('Email'))
        ->assertSee(__('Open tasks here'))
        ->assertSee(__('Add'))
        ->assertSee(__('Remove'))
        ->assertSee('Jamie Lee')
        ->assertSee('Alex Rivera', false);
});

test('members tab shows open team task counts per member', function () {
    $member = User::factory()->create(['name' => 'Jamie Lee', 'email' => 'jamie@example.com']);
    $otherMember = User::factory()->create(['name' => 'Alex Kim', 'email' => 'alex@example.com']);
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach([$member->id, $otherMember->id]);

    $openStatus = Status::factory()->create(['slug' => 'pending', 'is_closed' => false]);
    $closedStatus = Status::factory()->closed()->create(['slug' => 'completed']);

    $memberOpenTask = Task::query()->create(['title' => 'Jamie open task']);
    $memberOpenTask->setOwner($team);
    $memberOpenTask->syncTeams([$team]);
    $memberOpenTask->syncAssignees([$member]);
    $memberOpenTask->setStatus($openStatus);

    $memberClosedTask = Task::query()->create(['title' => 'Jamie closed task']);
    $memberClosedTask->setOwner($team);
    $memberClosedTask->syncTeams([$team]);
    $memberClosedTask->syncAssignees([$member]);
    $memberClosedTask->setStatus($closedStatus);

    $otherMemberTask = Task::query()->create(['title' => 'Alex open task']);
    $otherMemberTask->setOwner($team);
    $otherMemberTask->syncTeams([$team]);
    $otherMemberTask->syncAssignees([$otherMember]);
    $otherMemberTask->setStatus($openStatus);

    $this->actingAs($member)
        ->get(route('teams.show', ['team' => $team, 'tab' => 'members']))
        ->assertSuccessful()
        ->assertSee('jamie@example.com')
        ->assertSee('alex@example.com')
        ->assertSeeInOrder([
            'Jamie Lee',
            'jamie@example.com',
            '1',
            'Alex Kim',
            'alex@example.com',
            '1',
        ]);
});

test('users who are not team members cannot view private teams', function () {
    $member = User::factory()->create(['name' => 'Team Member']);
    $viewer = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $team->members()->attach($member);

    $project = Project::factory()->teamOwned()->create(['title' => 'Hidden platform rollout']);
    $project->syncTeams([$team]);
    $project->setStatus(Status::factory()->create(['slug' => 'pending', 'is_closed' => false]));

    $this->actingAs($viewer)
        ->get(route('teams.show', $team))
        ->assertNotFound();
});

test('projects tab shows an empty state on public teams without projects', function () {
    $user = User::factory()->create();
    $team = Team::factory()->public()->create(['name' => 'Empty Team']);

    $this->actingAs($user)
        ->get(route('teams.show', ['team' => $team, 'tab' => 'projects']))
        ->assertSuccessful()
        ->assertSee('Empty Team')
        ->assertSee(__('No projects are available for this team yet.'));
});
