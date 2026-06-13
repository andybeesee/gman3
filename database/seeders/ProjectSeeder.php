<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\Concerns\SeedsVisibilityGrants;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    use SeedsVisibilityGrants;

    private const TARGET_PROJECT_COUNT = 250;

    private const TASKS_PER_PROJECT_MIN = 5;

    private const TASKS_PER_PROJECT_MAX = 25;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projectsToCreate = self::TARGET_PROJECT_COUNT - Project::query()->count();

        if ($projectsToCreate <= 0) {
            return;
        }

        $users = User::query()->get();
        $teams = Team::query()->get();

        for ($i = 0; $i < $projectsToCreate; $i++) {
            $this->seedProject($users, $teams);
        }
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Team>  $teams
     */
    protected function seedProject(Collection $users, Collection $teams): void
    {
        if ($users->isEmpty()) {
            return;
        }

        $creator = $users->random();
        $factory = Project::factory();
        $roll = fake()->numberBetween(1, 100);

        if ($roll <= 15) {
            $factory = $factory->public($creator);
        } elseif ($roll <= 35) {
            $factory = $factory->privateForUser($creator);
        } else {
            $factory = $factory->privateForTeam($creator);

            if ($teams->isNotEmpty()) {
                $factory = $factory->afterCreating(function (Project $project) use ($teams): void {
                    $project->syncTeams($teams->random(fake()->numberBetween(1, min(2, $teams->count()))));
                });
            }
        }

        $project = $factory->create();

        $this->maybeSeedVisibilityGrants($project, $users, $teams);

        $taskCount = fake()->numberBetween(self::TASKS_PER_PROJECT_MIN, self::TASKS_PER_PROJECT_MAX);

        Task::factory()
            ->count($taskCount)
            ->forProject($project)
            ->create();
    }
}
