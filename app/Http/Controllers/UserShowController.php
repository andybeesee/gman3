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

        $showHierarchyTab = $request->user()->can('updateHierarchy', User::class);
        $tab = $this->resolveTab($request, $showHierarchyTab);

        $user->loadCount('subordinates');
        $user->open_tasks_count = $user->relatedTasksQuery()->whereStatusOpen()->count();

        if ($tab === 'hierarchy') {
            $user->load('supervisor');
        }

        return view('users.show', [
            'user' => $user,
            'tab' => $tab,
            'showHierarchyTab' => $showHierarchyTab,
            ...$this->dataForTab($user, $tab),
        ]);
    }

    private function resolveTab(Request $request, bool $showHierarchyTab): string
    {
        $tab = $request->query('tab', 'dashboard');
        $allowedTabs = self::TABS;

        if ($showHierarchyTab) {
            $allowedTabs[] = 'hierarchy';
        }

        return in_array($tab, $allowedTabs, true) ? $tab : 'dashboard';
    }

    /**
     * @return array<string, mixed>
     */
    private function dataForTab(User $user, string $tab): array
    {
        return match ($tab) {
            'projects' => $this->projectsTabData($user),
            'tasks' => $this->tasksTabData($user),
            'hierarchy' => $this->hierarchyTabData($user),
            default => $this->dashboardTabData($user),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function dashboardTabData(User $user): array
    {
        return [
            'subordinates' => $user->subordinates()->orderBy('name')->get(),
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
            'supervisedUsers' => collect(),
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
            'subordinates' => collect(),
            'activeProjects' => collect(),
            'activeTasks' => collect(),
            'statuses' => collect(),
            'projects' => $projects,
            'tasks' => new LengthAwarePaginator([], 0, 50),
            'supervisedUsers' => collect(),
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
            'subordinates' => collect(),
            'activeProjects' => collect(),
            'activeTasks' => collect(),
            'statuses' => Status::query()->orderBy('sort_order')->get(),
            'projects' => new LengthAwarePaginator([], 0, 50),
            'tasks' => $tasks,
            'supervisedUsers' => collect(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function hierarchyTabData(User $user): array
    {
        return [
            'subordinates' => collect(),
            'activeProjects' => collect(),
            'activeTasks' => collect(),
            'statuses' => collect(),
            'projects' => new LengthAwarePaginator([], 0, 50),
            'tasks' => new LengthAwarePaginator([], 0, 50),
            'supervisedUsers' => $user->subordinates()->orderBy('name')->get(),
        ];
    }
}
