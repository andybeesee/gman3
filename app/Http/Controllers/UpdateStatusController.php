<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UpdateStatusController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Status $status): RedirectResponse
    {
        $this->authorize('update', $status);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('statuses', 'slug')->ignore($status)],
            'icon' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'in:gray,blue,orange,green,red,yellow,purple,pink'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_closed' => ['boolean'],
        ]);

        $status->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'icon' => $validated['icon'],
            'color' => $validated['color'],
            'sort_order' => $validated['sort_order'],
            'is_closed' => $request->boolean('is_closed'),
        ]);

        return redirect()->route('statuses.show', $status)
            ->with('status', __('Status updated.'));
    }
}
