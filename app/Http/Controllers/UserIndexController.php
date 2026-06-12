<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class UserIndexController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display all users visible to the authenticated user.
     */
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->visibleTo($request->user())
            ->select('users.*')
            ->withCount('subordinates')
            ->withCount([
                'assignedTasks as open_tasks_count' => fn ($query) => $query->whereStatusOpen(),
            ])
            ->orderBy('name')
            ->paginate(50);

        return view('users.index', [
            'users' => $users,
        ]);
    }
}
