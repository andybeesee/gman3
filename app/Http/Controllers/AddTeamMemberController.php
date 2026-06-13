<?php

namespace App\Http\Controllers;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

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
            'role' => ['nullable', new Enum(TeamRole::class)],
        ]);

        $role = isset($validated['role'])
            ? TeamRole::from($validated['role'])
            : TeamRole::Member;

        $team->addMember(
            User::query()->findOrFail($validated['user_id']),
            $role,
        );

        return redirect()
            ->route('teams.show', ['team' => $team, 'tab' => 'members'])
            ->with('status', __('Member added.'));
    }
}
