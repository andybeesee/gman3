<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AddTeamMemberController extends Controller
{
    use AuthorizesRequests;

    /**
     * Add a user to the given team.
     */
    public function __invoke(Request $request, Team $team): RedirectResponse
    {
        $this->authorize('updateMembers', $team);

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::notIn($team->members()->pluck('users.id')),
            ],
        ]);

        $team->members()->attach($validated['user_id']);

        return redirect()
            ->route('teams.show', ['team' => $team, 'tab' => 'members'])
            ->with('status', __('Member added.'));
    }
}
