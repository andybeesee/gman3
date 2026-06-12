<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RemoveUserSubordinateController extends Controller
{
    use AuthorizesRequests;

    /**
     * Remove a user from the given supervisor's direct reports.
     */
    public function __invoke(Request $request, User $user, User $subordinate): RedirectResponse
    {
        $this->authorize('updateHierarchy', User::class);

        abort_unless($subordinate->supervisor_id === $user->id, 404);

        $subordinate->forceFill([
            'supervisor_id' => null,
        ])->save();

        return redirect()
            ->route('users.show', ['user' => $user, 'tab' => 'hierarchy'])
            ->with('status', __('Supervision removed.'));
    }
}
