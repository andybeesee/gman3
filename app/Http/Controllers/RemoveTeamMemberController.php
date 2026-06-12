<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RemoveTeamMemberController extends Controller
{
    use AuthorizesRequests;

    /**
     * Remove a user from the given team.
     */
    public function __invoke(Request $request, Team $team, User $member): RedirectResponse
    {
        $this->authorize('updateMembers', $team);

        abort_unless($team->members()->whereKey($member->id)->exists(), 404);

        $team->members()->detach($member);

        return redirect()
            ->route('teams.show', ['team' => $team, 'tab' => 'members'])
            ->with('status', __('Member removed.'));
    }
}
