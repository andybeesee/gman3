<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CreateTaskController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request): View
    {
        $this->authorize('create', Task::class);

        return view('tasks.create');
    }
}
