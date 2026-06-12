<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectIndexController;
use App\Http\Controllers\ProjectShowController;
use App\Http\Controllers\TaskIndexController;
use App\Http\Controllers\TeamIndexController;
use App\Http\Controllers\TeamShowController;
use App\Http\Controllers\UpdateTaskStatusController;
use App\Http\Controllers\UpdateTeamMembersController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/tasks', TaskIndexController::class)->name('tasks.index');
    Route::get('/projects', ProjectIndexController::class)->name('projects.index');
    Route::get('/projects/{project}', ProjectShowController::class)->name('projects.show');
    Route::get('/teams', TeamIndexController::class)->name('teams.index');
    Route::get('/teams/{team}', TeamShowController::class)->name('teams.show');
    Route::put('/teams/{team}/members', UpdateTeamMembersController::class)->name('teams.members.update');
    Route::patch('/tasks/{task}/status', UpdateTaskStatusController::class)->name('tasks.status.update');
});
