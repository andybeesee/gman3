<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskIndexController;
use App\Http\Controllers\TeamIndexController;
use App\Http\Controllers\UpdateTaskStatusController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/tasks', TaskIndexController::class)->name('tasks.index');
    Route::get('/teams', TeamIndexController::class)->name('teams.index');
    Route::patch('/tasks/{task}/status', UpdateTaskStatusController::class)->name('tasks.status.update');
});
