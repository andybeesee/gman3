<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TeamIndexController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display all teams across the organization.
     */
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Team::class);

        $teams = Team::query()
            ->visibleTo($request->user())
            ->select('teams.*')
            ->withCount('members')
            ->selectSub(
                Task::query()
                    ->selectRaw('count(*)')
                    ->whereStatusOpen()
                    ->forTeamColumn('teams.id'),
                'open_tasks_count',
            )
            ->orderBy('name')
            ->paginate(50);

        return view('teams.index', [
            'teams' => $teams,
        ]);
    }
}
