<?php

namespace App\Http\Controllers;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class UpdateTeamMemberRoleController extends Controller
{
    use AuthorizesRequests;

    /**
     * Update the team role for the given member.
     */
    public function __invoke(Request $request, Team $team, User $member): JsonResponse|RedirectResponse
    {
        $this->authorize('updateMembers', $team);

        $validated = $request->validate([
            'role' => ['required', new Enum(TeamRole::class)],
        ]);

        abort_unless($team->members()->whereKey($member->id)->exists(), 404);

        $newRole = TeamRole::from($validated['role']);
        $currentRole = $team->roleFor($member);

        if ($currentRole === TeamRole::Leader && $newRole === TeamRole::Member && $team->leaderCount() <= 1) {
            $message = __('Each team must keep at least one leader.');

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'errors' => [
                        'role' => [$message],
                    ],
                ], 422);
            }

            return redirect()
                ->route('teams.show', ['team' => $team, 'tab' => 'members'])
                ->withErrors([
                    'role.'.$member->id => $message,
                ]);
        }

        $team->members()->updateExistingPivot($member->id, [
            'role' => $newRole->value,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('Member role updated.'),
                'member' => [
                    'id' => $member->id,
                    'role' => $newRole->value,
                    'role_label' => $newRole->label(),
                ],
            ]);
        }

        return redirect()
            ->route('teams.show', ['team' => $team, 'tab' => 'members'])
            ->with('status', __('Member role updated.'));
    }
}
