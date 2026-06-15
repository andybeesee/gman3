<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StatusShowController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Status $status): View
    {
        $this->authorize('view', $status);

        return view('statuses.show', ['status' => $status]);
    }
}
