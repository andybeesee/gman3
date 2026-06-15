<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreStatusController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request): RedirectResponse
    {
        $this->authorize('create', Status::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:statuses,slug'],
            'icon' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'in:gray,blue,orange,green,red,yellow,purple,pink'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_closed' => ['boolean'],
        ]);

        $status = Status::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'icon' => $validated['icon'],
            'color' => $validated['color'],
            'sort_order' => $validated['sort_order'],
            'is_closed' => $request->boolean('is_closed'),
        ]);

        return redirect()->route('statuses.show', $status)
            ->with('status', __('Status created.'));
    }
}
