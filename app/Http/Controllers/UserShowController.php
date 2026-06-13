<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class UserShowController extends Controller
{
    use AuthorizesRequests;

    /**
     * @var list<string>
     */
    private const TABS = ['dashboard', 'projects', 'tasks'];

    private const DASHBOARD_LIMIT = 8;

    /**
     * Display the given user.
     */
    public function __invoke(Request $request, User $user): View
    {
        $this->authorize('view', $user);

        $tab = $this->resolveTab($request);
        $user->open_tasks_count = $user->relatedTasksQuery()->whereStatusOpen()->count();

        return view('users.show', [
            'user' => $user,
            'tab' => $tab,
            ...$this->dataForTab($user, $tab),
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
    private function dataForTab(User $user, string $tab): array
    {
        return match ($tab) {
            'projects' => $this->projectsTabData($user),
            'tasks' => $this->tasksTabData($user),
            default => $this->dashboardTabData($user),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function dashboardTabData(User $user): array
    {
        return [
            'activeProjects' => $user->ownedProjects()
                ->with(['currentStatusChange.status', 'teams'])
                ->withCount('ownedTasks')
                ->whereStatusOpen()
                ->orderByRaw('due_date IS NULL')
                ->orderBy('due_date')
                ->latest('id')
                ->limit(self::DASHBOARD_LIMIT)
                ->get(),
            'activeTasks' => $user->relatedTasksQuery()
                ->with(['currentStatusChange.status', 'assignees', 'owner', 'teams'])
                ->whereStatusOpen()
                ->orderByRaw('due_date IS NULL')
                ->orderBy('due_date')
                ->latest('id')
                ->limit(self::DASHBOARD_LIMIT)
                ->get(),
            'statuses' => Status::query()->orderBy('sort_order')->get(),
            'projects' => new LengthAwarePaginator([], 0, 50),
            'tasks' => new LengthAwarePaginator([], 0, 50),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function projectsTabData(User $user): array
    {
        $projects = $user->ownedProjects()
            ->with(['currentStatusChange.status', 'teams'])
            ->withCount('ownedTasks')
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('id')
            ->paginate(50)
            ->appends(['tab' => 'projects']);

        return [
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
    private function tasksTabData(User $user): array
    {
        $tasks = $user->relatedTasksQuery()
            ->with(['currentStatusChange.status', 'assignees', 'owner', 'teams'])
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('id')
            ->paginate(50)
            ->appends(['tab' => 'tasks']);

        return [
            'activeProjects' => collect(),
            'activeTasks' => collect(),
            'statuses' => Status::query()->orderBy('sort_order')->get(),
            'projects' => new LengthAwarePaginator([], 0, 50),
            'tasks' => $tasks,
        ];
    }
}
