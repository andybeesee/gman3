<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StatusCreateController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(): View
    {
        $this->authorize('create', Status::class);

        return view('statuses.create');
    }
}
