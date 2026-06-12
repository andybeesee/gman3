<?php

namespace App\Providers;

use App\Models\Task;
use App\Models\Team;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * When adding a model to any morph relation, register it here with a short alias.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'task' => Task::class,
            'team' => Team::class,
        ]);
    }
}
