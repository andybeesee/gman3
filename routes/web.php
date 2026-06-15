<?php

use App\Http\Controllers\AddTeamMemberController;
use App\Http\Controllers\ChecklistIndexController;
use App\Http\Controllers\ChecklistShowController;
use App\Http\Controllers\CreateTaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectIndexController;
use App\Http\Controllers\ProjectShowController;
use App\Http\Controllers\RemoveTeamMemberController;
use App\Http\Controllers\StatusCreateController;
use App\Http\Controllers\StatusEditController;
use App\Http\Controllers\StatusIndexController;
use App\Http\Controllers\StatusShowController;
use App\Http\Controllers\StoreProjectTaskController;
use App\Http\Controllers\StoreStatusController;
use App\Http\Controllers\StoreTaskController;
use App\Http\Controllers\TaskIndexController;
use App\Http\Controllers\TaskShowController;
use App\Http\Controllers\TeamIndexController;
use App\Http\Controllers\TeamShowController;
use App\Http\Controllers\UpdateChecklistTaskOrderController;
use App\Http\Controllers\UpdateStatusController;
use App\Http\Controllers\UpdateTaskDueDateController;
use App\Http\Controllers\UpdateTaskStatusController;
use App\Http\Controllers\UpdateTeamMemberRoleController;
use App\Http\Controllers\UserIndexController;
use App\Http\Controllers\UserShowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/checklists', ChecklistIndexController::class)->name('checklists.index');
    Route::get('/checklists/{checklist}', ChecklistShowController::class)->name('checklists.show');
    Route::patch('/checklists/{checklist}/tasks/order', UpdateChecklistTaskOrderController::class)->name('checklists.tasks.order.update');
    Route::get('/tasks', TaskIndexController::class)->name('tasks.index');
    Route::get('/tasks/create', CreateTaskController::class)->name('tasks.create');
    Route::post('/tasks', StoreTaskController::class)->name('tasks.store');
    Route::get('/tasks/{task}', TaskShowController::class)->name('tasks.show');
    Route::get('/projects', ProjectIndexController::class)->name('projects.index');
    Route::get('/projects/{project}', ProjectShowController::class)->name('projects.show');
    Route::post('/projects/{project}/tasks', StoreProjectTaskController::class)->name('projects.tasks.store');
    Route::get('/users', UserIndexController::class)->name('users.index');
    Route::get('/users/{user}', UserShowController::class)->name('users.show');
    Route::get('/teams', TeamIndexController::class)->name('teams.index');
    Route::get('/teams/{team}', TeamShowController::class)->name('teams.show');
    Route::post('/teams/{team}/members', AddTeamMemberController::class)->name('teams.members.store');
    Route::patch('/teams/{team}/members/{member}/role', UpdateTeamMemberRoleController::class)->name('teams.members.role.update');
    Route::delete('/teams/{team}/members/{member}', RemoveTeamMemberController::class)->name('teams.members.destroy');
    Route::patch('/tasks/{task}/status', UpdateTaskStatusController::class)->name('tasks.status.update');
    Route::patch('/tasks/{task}/due-date', UpdateTaskDueDateController::class)->name('tasks.due-date.update');
    Route::get('/statuses', StatusIndexController::class)->name('statuses.index');
    Route::get('/statuses/create', StatusCreateController::class)->name('statuses.create');
    Route::post('/statuses', StoreStatusController::class)->name('statuses.store');
    Route::get('/statuses/{status}', StatusShowController::class)->name('statuses.show');
    Route::get('/statuses/{status}/edit', StatusEditController::class)->name('statuses.edit');
    Route::patch('/statuses/{status}', UpdateStatusController::class)->name('statuses.update');
});
