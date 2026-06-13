<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserIndexController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display all users visible to the authenticated user.
     */
    public function __invoke(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->select('users.*')
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
