<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StatusIndexController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(): View
    {
        $this->authorize('viewAny', Status::class);

        $statuses = Status::query()->orderBy('sort_order')->get();

        return view('statuses.index', ['statuses' => $statuses]);
    }
}
