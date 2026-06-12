<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateTeamMembersController extends Controller
{
    use AuthorizesRequests;

    /**
     * Update the members assigned to the given team.
     */
    public function __invoke(Request $request, Team $team): RedirectResponse
    {
        $this->authorize('updateMembers', $team);

        $validated = $request->validate([
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $team->members()->sync($validated['user_ids'] ?? []);

        return redirect()
            ->route('teams.show', ['team' => $team, 'tab' => 'members'])
            ->with('status', __('Team members updated.'));
    }
}
