<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StatusEditController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Status $status): View
    {
        $this->authorize('update', $status);

        return view('statuses.edit', ['status' => $status]);
    }
}
