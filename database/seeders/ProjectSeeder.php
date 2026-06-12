<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    private const TARGET_PROJECT_COUNT = 40;

    private const TASKS_PER_PROJECT_MIN = 8;

    private const TASKS_PER_PROJECT_MAX = 28;

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

        for ($i = 0; $i < $projectsToCreate; $i++) {
            $this->seedProject($users);
        }
    }

    /**
     * @param  Collection<int, User>  $users
     */
    protected function seedProject(Collection $users): void
    {
        $factory = Project::factory();

        if (fake()->boolean(20) && $users->isNotEmpty()) {
            $factory = $factory->personallyOwnedBy($users->random());
        }

        $project = $factory->create();

        $taskCount = fake()->numberBetween(self::TASKS_PER_PROJECT_MIN, self::TASKS_PER_PROJECT_MAX);

        Task::factory()
            ->count($taskCount)
            ->forProject($project)
            ->create();
    }
}
