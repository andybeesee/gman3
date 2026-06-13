<?php

use App\Http\Controllers\AddTeamMemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectIndexController;
use App\Http\Controllers\ProjectShowController;
use App\Http\Controllers\RemoveTeamMemberController;
use App\Http\Controllers\TaskIndexController;
use App\Http\Controllers\TeamIndexController;
use App\Http\Controllers\TeamShowController;
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
    Route::get('/tasks', TaskIndexController::class)->name('tasks.index');
    Route::get('/projects', ProjectIndexController::class)->name('projects.index');
    Route::get('/projects/{project}', ProjectShowController::class)->name('projects.show');
    Route::get('/users', UserIndexController::class)->name('users.index');
    Route::get('/users/{user}', UserShowController::class)->name('users.show');
    Route::get('/teams', TeamIndexController::class)->name('teams.index');
    Route::get('/teams/{team}', TeamShowController::class)->name('teams.show');
    Route::post('/teams/{team}/members', AddTeamMemberController::class)->name('teams.members.store');
    Route::patch('/teams/{team}/members/{member}/role', UpdateTeamMemberRoleController::class)->name('teams.members.role.update');
    Route::delete('/teams/{team}/members/{member}', RemoveTeamMemberController::class)->name('teams.members.destroy');
    Route::patch('/tasks/{task}/status', UpdateTaskStatusController::class)->name('tasks.status.update');
});
