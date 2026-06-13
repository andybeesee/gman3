<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TeamShowController extends Controller
{
    use AuthorizesRequests;

    /**
     * @var list<string>
     */
    private const TABS = ['dashboard', 'members', 'projects', 'tasks'];

    private const DASHBOARD_LIMIT = 8;

    /**
     * Display the given team.
     */
    public function __invoke(Request $request, Team $team): View
    {
        $this->authorize('view', $team);

        $tab = $this->resolveTab($request);

        $team->loadCount([
            'members',
        ]);

        $team->open_tasks_count = $team->relatedTasksQuery()->whereStatusOpen()->count();

        return view('teams.show', [
            'team' => $team,
            'tab' => $tab,
            'canUpdateMembers' => $request->user()->can('updateMembers', $team),
            ...$this->dataForTab($request, $team, $tab),
        ]);
    }

    private function resolveTab(Request $request): string
    {
        $tab = $request->query('tab', 'dashboard');

        return in_array($tab, self::TABS, true) ? $tab : 'dashboard';
    }

    /**
     * @return array<string, mixed>
     */
    private function dataForTab(Request $request, Team $team, string $tab): array
    {
        return match ($tab) {
            'members' => $this->membersTabData($request, $team),
            'projects' => $this->projectsTabData($team),
            'tasks' => $this->tasksTabData($team),
            default => $this->dashboardTabData($team),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function dashboardTabData(Team $team): array
    {
        return [
            'members' => $team->members()->orderBy('name')->get(),
            'activeProjects' => $team->projects()
                ->with(['currentStatusChange.status', 'ownerUser', 'teams', 'visibilityGrants.grantee'])
                ->withCount('ownedTasks')
                ->whereStatusOpen()
                ->orderByRaw('due_date IS NULL')
                ->orderBy('due_date')
                ->latest('id')
                ->limit(self::DASHBOARD_LIMIT)
                ->get(),
            'activeTasks' => $team->relatedTasksQuery()
                ->with(['currentStatusChange.status', 'assignees', 'owner', 'teams', 'visibilityGrants.grantee'])
                ->whereStatusOpen()
                ->orderByRaw('due_date IS NULL')
                ->orderBy('due_date')
                ->latest('id')
                ->limit(self::DASHBOARD_LIMIT)
                ->get(),
            'statuses' => Status::query()->orderBy('sort_order')->get(),
            'nonMembers' => collect(),
            'projects' => new LengthAwarePaginator([], 0, 50),
            'tasks' => new LengthAwarePaginator([], 0, 50),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function membersTabData(Request $request, Team $team): array
    {
        return [
            'members' => $team->members()
                ->withCount([
                    'assignedTasks as open_team_tasks_count' => fn ($query) => $query
                        ->whereStatusOpen()
                        ->forTeam($team),
                ])
                ->orderBy('name')
                ->get(),
            'nonMembers' => User::query()
                ->whereDoesntHave('teams', fn ($query) => $query->whereKey($team->id))
                ->orderBy('name')
                ->get(),
            'activeProjects' => collect(),
            'activeTasks' => collect(),
            'statuses' => collect(),
            'projects' => new LengthAwarePaginator([], 0, 50),
            'tasks' => new LengthAwarePaginator([], 0, 50),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function projectsTabData(Team $team): array
    {
        $projects = $team->projects()
            ->with(['currentStatusChange.status', 'teams', 'ownerUser', 'visibilityGrants.grantee'])
            ->withCount('ownedTasks')
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('id')
            ->paginate(50)
            ->appends(['tab' => 'projects']);

        return [
            'members' => collect(),
            'nonMembers' => collect(),
            'activeProjects' => collect(),
            'activeTasks' => collect(),
            'statuses' => collect(),
            'projects' => $projects,
            'tasks' => new LengthAwarePaginator([], 0, 50),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function tasksTabData(Team $team): array
    {
        $tasks = $team->relatedTasksQuery()
            ->with(['currentStatusChange.status', 'assignees', 'owner', 'teams', 'visibilityGrants.grantee'])
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('id')
            ->paginate(50)
            ->appends(['tab' => 'tasks']);

        return [
            'members' => collect(),
            'nonMembers' => collect(),
            'activeProjects' => collect(),
            'activeTasks' => collect(),
            'statuses' => Status::query()->orderBy('sort_order')->get(),
            'projects' => new LengthAwarePaginator([], 0, 50),
            'tasks' => $tasks,
        ];
    }
}
